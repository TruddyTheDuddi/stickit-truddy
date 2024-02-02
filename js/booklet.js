import { PAGE_ROOT } from "./global.js";

// Use this to generate the booklet pages
const X = 0; const Y = 1;

// Behaves like a constructor for a page element
function createPage(data, editable=false){
    return {
        // Page
        _pageElement: null,

        // Data page what is changed
        _pageData: data,

        // Is this page editable
        isEditable: editable,

        /**
         * Renders the booklet page with the given data
         * @param {*} pageData 
         * @param {boolean} editable is this a sticker editor
         */
        generate: function() {
            this._pageElement = document.createElement("div");
            this._pageElement.classList.add("page");
            if (this.isEditable) this._pageElement.classList.add("editable");
            
            // Generate all stickers
            this._pageData.stickers.forEach(s => {
                // Create base
                let stickerDiv = document.createElement("div");
                stickerDiv.classList.add("sticker");
                stickerDiv.style.width = `${s.size}%`;
                stickerDiv.style.left = `${s.position[X]}%`;
                stickerDiv.style.top = `${s.position[Y]}%`;
                stickerDiv.setAttribute("data-sticker-id", s.id);
                
                // Create image holder
                let stickerImgHolder = document.createElement("div");
                stickerImgHolder.classList.add("sticker-img-holder");
                
                // Create image
                let stickerImg = document.createElement("img");
                stickerImg.style.transform = `rotate(${s.rotate}deg)`;
                stickerImg.src = PAGE_ROOT + `img/stickers/${s.file}`;
                stickerImgHolder.appendChild(stickerImg);
                stickerDiv.appendChild(stickerImgHolder);
            
                // Create action buttons
                if(editable){
                    let action = document.createElement("div");
                    action.classList.add("action");
                    let actBtn = [
                        {
                            // Increase size
                            img : 'size_plus',
                            action : (listItem) => {
                                if(listItem.size >= 70) return;
                                listItem.size += 2.5;
                                stickerDiv.style.width = `${listItem.size}%`;
                            }
                        },
                        {
                            // Decrease size
                            img : 'size_minus',
                            action : (listItem) => {
                                if(listItem.size <= 5) return;
                                listItem.size -= 2.5;
                                stickerDiv.style.width = `${listItem.size}%`;
                            }
                        },
                        {
                            // Rotate right
                            img : 'rotate_right',
                            action : (listItem) => {
                                listItem.rotate += 10;
                                stickerImg.style.transform = `rotate(${listItem.rotate}deg)`;
                            } 
                        },
                        {
                            // Rotate left
                            img : 'rotate_left',
                            action : (listItem) => {
                                listItem.rotate -= 10;
                                stickerImg.style.transform = `rotate(${listItem.rotate}deg)`;
                            } 
                        },
                    ];
            
                    if(editable){
                        actBtn.forEach(a => {
                            let btn = document.createElement("img");
                            btn.src = PAGE_ROOT + `img/icons/${a.img}.svg`;
                            btn.draggable = false;
            
                            // Setup behavior
                            btn.addEventListener("click", () => {
                                a.action(s);
                            })
            
                            action.appendChild(btn);
                        });
                    }
                    stickerDiv.appendChild(action);
                }
            
                this._pageElement.appendChild(stickerDiv);
                if (editable) this._setupDragging(stickerImgHolder, s);
            });
            
            return this._pageElement;
        },

        /**
         * Dragging behavior for the sticker
         * @param {*} stickerImgHolder 
         * @param {*} listElement element in the list for ordering
         */
        _setupDragging: function (stickerImgHolder, listElement){
            let isDragging = false;
            let dragPoint = [0, 0];
            let stickerBox = stickerImgHolder.parentElement;

            stickerImgHolder.addEventListener("mousedown", (e) => {
                setDragging(true);

                // Log where was click
                let rect = stickerBox.getBoundingClientRect();
                dragPoint = [e.clientX - rect.left - rect.width * 0.5, e.clientY - rect.top - rect.height * 0.5];

                // Bring to front by putting element as last child of page
                this._pageElement.appendChild(stickerBox);
                // Put sticker at end of list
                pageDummy.stickers.push(
                    pageDummy.stickers.splice(pageDummy.stickers.indexOf(listElement), 1)[0]
                );
            });

            this._pageElement.addEventListener("mouseup", () => {
                if(!isDragging) return;
                setDragging(false);
            });

            this._pageElement.addEventListener("mousemove", (e) => {
                if(!isDragging) return;

                let x = e.clientX; let y = e.clientY;
                let dx = x - dragPoint[0]; let dy = y - dragPoint[1];

                // Convert to percentage
                let rect = this._pageElement.getBoundingClientRect();
                let left = (dx - rect.left) / rect.width * 100;
                let top = (dy - rect.top) / rect.height * 100;

                // Update list element
                listElement.position = [left,top];

                stickerBox.style.left = `${left}%`;
                stickerBox.style.top = `${top}%`;
            
            });

            // Util function for styling
            function setDragging(bool){
                isDragging = bool;
                if(bool){
                    stickerBox.classList.add('dragging');
                } else {
                    stickerBox.classList.remove('dragging');
                }
            }  
        }

    };
}



let pageDummy = {
    title: "Truddy",
    stickers: [
        {
            id: 1,
            name: "Truddy Pissed",
            file: "1.png",
            position: [0, 0],
            size: 20,
            rotate: 5
        },
        {
            id: 2,
            name: "Truddy Smirk",
            file: "2.png",
            position: [20, 18],
            size: 40,
            rotate: -50
        },
        {
            id: 3,
            name: "Truddy Happy",
            file: "3.png",
            position: [40, 15],
            size: 50,
            rotate: -5
        },
        {
            id: 4,
            name: "Cat 4",
            file: "cat4.png",
            position: [70, 20],
            size: 30,
            rotate: 20
        },
        {
            id: 5,
            name: "Cat 1",
            file: "cat1.png",
            position: [80, 50],
            size: 20,
            rotate: 0
        },
        {
            id: 6,
            name: "Cat 2",
            file: "cat2.png",
            position: [50, 70],
            size: 30,
            rotate: 0
        },
        {
            id: 7,
            name: "Cat 3",
            file: "cat3.png",
            position: [15, 75],
            size: 20,
            rotate: 0
        }
    ]
};

let page = createPage(pageDummy, true);
document.querySelector("main").appendChild(page.generate());