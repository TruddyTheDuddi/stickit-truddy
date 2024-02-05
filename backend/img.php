<?php
include_once("tools.php");
include_once("user.php");


class Img{
    const MAX_FILE_SIZE = 1000*1000*8; // 8 Mb
    const FILE_DIM_X = 2000;
    const FILE_DIM_Y = 2000;
    const ALLOWED_PIC = array("image/png", "image/jpeg", "image/jpg", "image/webp");

    const WEBP_QUALITY = 80; // Compression quality

    const STICKER_DIR_PLAIN  = DOC_ROOT."img/stickers/plain/";
    const STICKER_DIR_SECRET = DOC_ROOT."img/stickers/secret/";

    /**
     * Validates an images by checking basic info
     * @param file $file for picture to check as from $_FILES format
     */
    private static function run_validation($file){
        global $db;

        // TODO: Check if uploads allowed
    
        // Error uploading
        if ($file['error'] !== 0 OR $file['size'] == 0) {
            throw new Exception("Something went wrong. Could you try that again?");
        }
    
        // Metadata
        $fName = make_sql_safe($file['name']);
        $fPath = make_sql_safe($file['tmp_name']);
        $fSize = make_sql_safe($file['size']);
        $fExt  = strtolower(make_sql_safe(pathinfo($fName)['extension']));
    
        // Correct file type
        $fileMime = mime_content_type($fPath);
        if ($fileMime === false || !in_array($fileMime, Img::ALLOWED_PIC)) {
            throw new Exception("The type of file you uploaded is invalid or seems to be corrupted. Try only png, jpeg, webp or jpg!");
        }
        
        // Image size is to big
        if ($fSize > Img::MAX_FILE_SIZE){
            throw new Exception("The image is too big! File should be max ".(Img::MAX_FILE_SIZE / (1000.0*1000.0))." Mb");
        }
    }

    /**
     * Processing the image by perfomring validation, moving, 
     * compression
     */
    public static function image_process($file){    
        // Validate image
        Img::run_validation($file);
        
        // Let compressor handle the file
        $name = Img::image_convert(make_sql_safe($file['tmp_name']), Img::STICKER_DIR_PLAIN.hash("md5", uniqid()), 80);
    
        return $name;
    }

    /**
     * Takes a file path and converts it to a webp image
     * @param string $file_path Path to file
     * @param string $save_path Path to save file
     * @return string the name of the file (name.extension)
     */
    public static function image_convert($file_path, $save_path){
        list($x, $y, $type) = getimagesize($file_path);
    
        // Rescale image if too big
        if ($x > self::FILE_DIM_X || $y > self::FILE_DIM_Y) {
            $ratio = $x / $y;
            if ($ratio > 1) {
                $x = self::FILE_DIM_X;
                $y = self::FILE_DIM_X / $ratio;
            } else {
                $x = self::FILE_DIM_Y * $ratio;
                $y = self::FILE_DIM_Y;
            }
        }
    
        $file_img_new = imagecreatetruecolor($x, $y);
        imagealphablending($file_img_new, false);
        imagesavealpha($file_img_new, true);
        $transparency = imagecolorallocatealpha($file_img_new, 255, 255, 255, 127);
        imagefilledrectangle($file_img_new, 0, 0, $x, $y, $transparency);
    
        switch ($type) {
            case IMAGETYPE_JPEG:
                $file_img_orig = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                $file_img_orig = imagecreatefrompng($file_path);
                break;
            case IMAGETYPE_WEBP:
                $file_img_orig = imagecreatefromwebp($file_path);
                break;
            default:
                throw new Exception('Unsupported image type. Only JPEG and PNG are allowed.');
        }
    
        imagecopyresampled($file_img_new, $file_img_orig, 0, 0, 0, 0, $x, $y, imagesx($file_img_orig), imagesy($file_img_orig));
    
        // Save as WebP
        $save_path_webp = $save_path . ".webp";
        imagewebp($file_img_new, $save_path_webp, self::WEBP_QUALITY);
    
        imagedestroy($file_img_orig);
        imagedestroy($file_img_new);
    
        return pathinfo($save_path_webp)['basename'];
    }
    
    /**
     * Create a black image for the mystery sticker where anything
     * but alpha channel is turned into black
     * @param string $file_path Path to file
     * @param string $save_path Path to save file
     * @return string the name of the file (name.extension)
     */
    public static function createBlackImageWithAlpha($file_path, $save_path) {
        // Ensure the file is a WebP image
        if (mime_content_type($file_path) !== 'image/webp') {
            throw new Exception("The file must be a WebP image.");
        }

        // Create image from WebP file
        $original = imagecreatefromwebp($file_path);
        if (!$original) {
            throw new Exception("Failed to open WebP image.");
        }

        $width = imagesx($original);
        $height = imagesy($original);

        // Create a true color image with the same size
        $blackImage = imagecreatetruecolor($width, $height);

        // Allocate black color
        $black = imagecolorallocate($blackImage, 0, 0, 0);
        imagefill($blackImage, 0, 0, $black);

        // Enable alpha blending and save alpha
        imagealphablending($blackImage, false);
        imagesavealpha($blackImage, true);

        // Copy only the alpha channel from the original to the new image
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $alpha = (imagecolorat($original, $x, $y) & 0x7F000000) >> 24;
                imagesetpixel($blackImage, $x, $y, imagecolorallocatealpha($blackImage, 0, 0, 0, $alpha));
            }
        }

        // Save the new image as WebP
        $save_path_webp = $save_path . ".webp";
        imagewebp($blackImage, $save_path_webp);

        // Cleanup
        imagedestroy($original);
        imagedestroy($blackImage);

        return basename($save_path_webp);
    }

}

// print_r($_FILES);

if(isset($_FILES['test'])){
    // echo "submitted image".$_FILES['test']['name'];
    $normal = Img::image_process($_FILES['test'], true);
    echo "<img src='".WEBSITE_SERVE."img/stickers/plain/".$normal."' height=300px>";
    
    $hidden = Img::createBlackImageWithAlpha(IMG::STICKER_DIR_PLAIN.$normal, Img::STICKER_DIR_SECRET.hash("md5", uniqid()));
    echo "<img src='".WEBSITE_SERVE."img/stickers/secret/".$hidden."' height=300px>";
}

?>

<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="test" id="">
    <input type="submit">
</form>