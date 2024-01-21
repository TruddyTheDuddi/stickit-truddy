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
