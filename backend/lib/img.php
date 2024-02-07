<?php
require_once("tools.php");
require_once("user.php");

/**
 * This class performs verification, and manipulations on images.
 * It always creates `.webp` images.
 * 
 * After the manipulation is done, it will put the file in a tmp
 * folder and that path will be returned after completion so that 
 * callee can decide what to do with it.
 */
class Img{
    const MAX_FILE_SIZE = 1000*1000*8; // 8 Mb
    const FILE_DIM_X = 1500;
    const FILE_DIM_Y = 1500;
    const ALLOWED_PIC = array("image/png", "image/jpeg", "image/jpg", "image/webp");

    const WEBP_QUALITY = 80; // Compression quality

    const TMP_DIR = DOC_ROOT."img/tmp/";

    /**
     * Creates a new file path for a `.webp` image, 
     * inside of the tmp folder.
     */
    private static function create_unique_file(){
        $file_name = hash("md5", uniqid());
        return Img::TMP_DIR.$file_name.".webp";
    }

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
        $fPath = make_sql_safe($file['tmp_name']);
        $fSize = make_sql_safe($file['size']);
    
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
     * @param file $file for picture as from $_FILES format
     * @return string Path
     */
    public static function image_process($file){    
        // Validate image
        Img::run_validation($file);

        // Let compressor handle the file
        $path = Img::image_convert(make_sql_safe($file['tmp_name']));
        return $path;
    }

    /**
     * Takes a file path, optimizes and converts it to a 
     * webp image.
     * @param string $file_path Path to original file
     * @return string Path to the new file
     */
    public static function image_convert($file_path){
        // Random name
        $save_path = Img::create_unique_file();

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
        imagewebp($file_img_new, $save_path, self::WEBP_QUALITY);
    
        imagedestroy($file_img_orig);
        imagedestroy($file_img_new);
    
        return $save_path;
    }
    
    /**
     * Create a black image for the mystery sticker where anything
     * but alpha channel is turned into black.
     * @param string $file_path Path to original file (must be `.webp`)
     * @return string Path to the new file
     */
    public static function create_secret_sticker($file_path) {
        // Random name
        $save_path = Img::create_unique_file();

        // Ensure the file is a WebP image
        if (mime_content_type($file_path) !== 'image/webp') {
            throw new Exception("The file must be a WebP image.");
        }

        // Create image from WebP file
        $original = imagecreatefromwebp($file_path);
        if (!$original) {
            throw new Exception("Failed to open WebP image.");
        }

        $width = imagesx($original); $height = imagesy($original);

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
        imagewebp($blackImage, $save_path);

        // Cleanup
        imagedestroy($original);
        imagedestroy($blackImage);

        return $save_path;
    }

    /**
     * Move a file by keeping the same name to a 
     * different. Use ImgPaths constants for the $to 
     * parameter.
     * @param string $from Location of the current file
     * @param ImgPaths $to folder where to save
     * @return string File's new path
     */
    public static function move($from, $to) {
        // Extract the filename
        $fileName = basename($from);

        // Build the destination path by appending the filename to the $to path
        $destination = $to . $fileName;

        // Move the file
        if (!rename($from, $destination)) {
            throw new Exception("Failed to move the file.");
        }

        return $destination;
    }
}

/**
 * Defining paths for moving images around
 * easily on the server.
 */
class ImgPaths{
    const PATH_STICKERS_HIDDEN = DOC_ROOT."img/stickers/shadow/";
    const PATH_STICKERS_REVEAL = DOC_ROOT."img/stickers/reveal/";
}

?>
