const PAGE_ROOT = "http://localhost:8888/booklet/";
const UTILTY_PAGES = {
    "error_404": PAGE_ROOT + "404.html",
}

/**
 * Create an element but on steroids
 * @param {*} tag 
 * @param {*} props 
 * @returns 
 */
function createEl(tag, props) {
    let el = document.createElement(tag);
    for (let prop in props) {
        if (prop === 'classList') {
            el.classList.add(...props[prop].split(' ')); // Supports a space-separated class list
        } else {
            el[prop] = props[prop];
        }
    }
    return el;
}

/**
 *
 * @param {*} nav element containing <a>'s to setup
 * @param {*} callback to provide that will trigger when page 
 *  changes with the hash as the argument
 */
function setupNav(nav, callback = null){
    let content = {};
    let currentHash = null;

    // Grab all the nav items
    nav.querySelectorAll("a").forEach(item => {
        let hash = item.getAttribute("href");
        // Log the item
        content[hash] = {
            a: item,
            hash: item.getAttribute("href"),
            page: document.querySelector(item.getAttribute("href")),
        };

        // Add click event for transition
        item.addEventListener("click", (e) => {
            e.preventDefault();
            
            // Set hash without triggering the hashchange event
            history.pushState(null, null, hash);
            
            // Smooth scroll to top
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
            transitionPage(hash);
        });
    });

    // Grab default either from hash or first item
    let defaultPage = window.location.hash ? window.location.hash : Object.keys(content)[0];
    currentHash = defaultPage;

    if(!content[defaultPage]){
        // If the page does not exist, go to 404
        window.location.href = UTILTY_PAGES.error_404;        
    }
    transitionPage(defaultPage, false);

    // Transition to the page when the hash changes
    window.addEventListener("hashchange", () => {
        if(content[window.location.hash]) {
            transitionPage(window.location.hash, false);
        } else {
            // If the page does not exist, go to 404
            window.location.href = UTILTY_PAGES.error_404;
        }
    });

    let setTimeoutId = null;
    /**
     * Transition to a page
     * @param {*} hash 
     * @param {boolean} transition whether to transition animate or not
     */
    function transitionPage(hash, transition = true){
        // When clicked, remove the active class from all items and hide all pages
        for(let key in content){
            content[key].a.classList.remove("active");
            content[key].page.classList.add("hidden");
            content[key].page.classList.remove("fade-show");
        }

        // Then add it to the clicked item
        content[hash].a.classList.add("active");

        // Create animation effect
        if(transition){
            content[currentHash].page.classList.remove("hidden");
            content[currentHash].page.classList.add("fade","fade-hide");

            // Clear the timeout if it exists
            setTimeoutId != null ? clearTimeout(setTimeoutId) : null;

            // Set the timeout
            setTimeoutId = setTimeout(() => {
                content[currentHash].page.classList.remove("fade","fade-hide");
                content[currentHash].page.classList.add("hidden");
                content[hash].page.classList.remove("hidden");
                content[hash].page.classList.add("fade","fade-hidden");
                currentHash = hash;
                // Mini timeout to allow the fade-hidden to be applied
                setTimeout(() => {
                    content[hash].page.classList.remove("fade","fade-hidden");
                    content[hash].page.classList.add("fade","fade-show");
                }, 10);
            }, 500);
        } else {
            content[hash].page.classList.remove("hidden");
        }


        // And show the corresponding page

        // Trigger the callback if provided
        if(callback){
            callback(hash);
        }

        // 
    }
}


// Beatify all input fields
let inputs = document.querySelectorAll(".input");
inputs.forEach(input => {

    // Add the message field between the .input-field and .input-desc if not already there
    if(!input.querySelector(".input-msg")) {
        let msg = createEl("div", {classList: "input-msg"});
        let img = createEl("img", {src: "img/icon/icon_warn.svg", classList: "msg-icon"});
        let text = createEl("span", {id: "msg-text"});
        
        msg.appendChild(img);
        msg.appendChild(text);
        input.insertBefore(msg, input.querySelector(".input-desc"));
    }

    // Add function to show an error
    let timeout = null;
    input.showError = function (msg) {
        // Add error class
        input.classList.add("error");

        // Add error message
        let error = input.querySelector("#msg-text");
        error.innerHTML = msg;

        // Add shake on the input field
        let inputField = input.querySelector(".input-field");

        // Reset animation trick
        inputField.style.animation = 'none';
        inputField.offsetHeight; /* trigger reflow */
        inputField.style.animation = null;

        // Trigger animation
        inputField.classList.add("shake");
        timeout != null ? clearTimeout(timeout) : null;
        timeout = setTimeout(() => {
            inputField.classList.remove("shake");
        }, 300);
    };

    // Fuction to gete data and info about the input
    input.getData = function () {
        let inputRaw = input.querySelector("input");
        let data = {
            name: inputRaw.name,
            value: inputRaw.type === "checkbox" ? inputRaw.checked : inputRaw.value.trim(),
            type: inputRaw.type,
        };
        return data;
    };

    // Clear error when clicking on message
    input.querySelector(".input-msg").addEventListener("click", () => {
        input.classList.remove("error");
    });
    
    // Clear error when typing something new
    input.querySelector("input").addEventListener("keydown", () => {
        input.classList.remove("error");
    });

    // Clear error when clicking on the input field if checkbox
    if(input.querySelector("input").type === "checkbox"){
        input.querySelector("input").addEventListener("click", () => {
            input.classList.remove("error");
        });
    }

});
