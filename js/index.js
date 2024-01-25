const PAGES = {
    create: document.getElementById("create-account"),
    login: document.getElementById("login"),
}

// Setup page navigation, with callback to make sure the page change between create and login is handled
setupNav((hash) => {
    if(hash == "#create-account"){
        currentPage = PAGES.create;
    } else if (hash == "#login") {
        currentPage = PAGES.login;
    }
});

/**
 * Gather the input fields given an element (panel)
 * and return 2 objects: one with the data, one with 
 * the html elements.
 * @param {HTMLElement} panel
 * @returns {object} - {data: {name: value}, elements: {name: element}}
 */
function gatherInputs(panel) {
    let data = {};
    let elements = {};
    
    let inputs = panel.querySelectorAll(".input");
    for (let i = 0; i < inputs.length; i++) {
        // Get input properties on the field
        let inputData = inputs[i].getData();

        // Add to data and element array
        data[inputData.name] = inputData['value'];
        elements[inputData.name] = inputs[i];
    }

    return {
        data: data,
        elements: elements
    };
}

// Create new account process containing each step and submits
let createSteps = {
    // Enter an email address to recieve a code
    email: {
        panel: document.getElementById("auth-email"),
        subPanel: document.getElementById("auth-email-send"),
        skip: document.getElementById("auth-email-skip"),
        submit: function() {
            let { data, elements } = gatherInputs(createSteps.email.subPanel);

            // No email entered
            if(data.email.length == 0) {
                elements.email.showError("Huh? Enter an email address please.");
                return;
            }

            // Email regex validation, apparently best one:
            if(!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test(data.email)) {
                elements.email.showError("This doesn't look like a valid email address to me. Please try another one!");
                return;
            }

            // TOS not accepted
            if(!data.tos) {
                elements.tos.showError("You must accept these terms to continue!");
                return;
            }

            AJAX.get("backend/api/user/user_create_code.php", {
                success: (resData) => {
                    if(resData.success){
                        goTo(createSteps.email.next);
                        createData.email = data.email;
                        document.getElementById("targetEmail").innerHTML = data.email;
                    } else {
                        elements.email.showError(resData.msg);
                    }
                }
            }, data);
        }
    },

    // Enter the code received by email
    code: {
        panel: document.getElementById("auth-email"),
        subPanel: document.getElementById("auth-email-code"),
        skip: document.getElementById("auth-code-back"),
        submit: function() {
            let { data, elements } = gatherInputs(createSteps.code.subPanel);

            // No code entered
            if(data.code.length == 0) {
                elements.code.showError("Please enter the code you received by email.");
                return;
            }

            // Add email to data
            data.email = createData.email;

            AJAX.get("backend/api/user/check_code.php", {
                success: (data) => {
                    if(data.success){
                        goTo(createSteps.codeAndEmail.next);
                        createData.code = data.code;
                    } else {
                        elements.code.showError(data.msg);
                    }
                }
            }, data);
        }
    },

    // Code already sent to an address
    codeAndEmail: {
        panel: document.getElementById("auth-email"),
        subPanel: document.getElementById("auth-email-code-old"),
        skip: document.getElementById("auth-code-email-back"),
        submit: function() {
            let { data, elements } = gatherInputs(createSteps.codeAndEmail.subPanel);

            // No email entered
            if(data.email.length == 0) {
                elements.email.showError("Huh? Enter an email address please!");
                return;
            }

            // No code entered
            if(data.code.length == 0) {
                elements.code.showError("Please enter the code you received by email.");
                return;
            }

            AJAX.get("backend/api/user/check_code.php", {
                success: (data) => {
                    if(data.success){
                        goTo(createSteps.codeAndEmail.next);
                        createData.code = data.code;
                        createData.email = data.email;
                    } else {
                        if(data.status == "email"){
                            elements.email.showError(data.msg);
                        } else {
                            elements.code.showError(data.msg);
                        }
                    }
                }
            }, data);
        }
    },

    // Confirm new credentials: username, password
    credentials: {
        panel: document.getElementById("auth-credentials"),
        subPanel: document.getElementById("auth-cred"),
        submit: function() {
            let { data, elements } = gatherInputs(createSteps.credentials.panel);

            // No username entered
            if(data.username.length == 0) {
                elements.username.showError("Oh! But what should we call you then? Enter a username please!");
                return;
            }

            // Regex username validation (4 to 16 characters), can contain - and _
            if(!/^[a-zA-Z0-9_-]{4,16}$/.test(data.username)) {
                elements.username.showError("This username doesn't seem to meet the requirements. Please try another one!");
                return;
            }

            // No password entered
            if(data.password.length == 0) {
                elements.password.showError("You need a strong password to protect your account.");
                return;
            }

            // Password regex validation at least 10 characters long
            if(data.password.length < 10) {
                elements.password.showError("Your password must be at least 10 characters long.");
                return;
            }

            // Passwords don't match
            if(data.password != data.password_confirm) {
                elements.password_confirm.showError("Passwords don't match.");
                return;
            }

            // Add email and code to payload
            data.email = createData.email;
            data.code = createData.code;

            AJAX.get("backend/api/user/user_create_account.php", {
                success: (data) => {
                    if(data.success){
                        // goTo(createSteps.credentials.next);
                        // createData.userId = data.userId;
                        console.log(data);
                    } else {
                        if(data.status == "username"){
                            elements.username.showError(data.msg);
                        } else if(data.status == "password") {
                            elements.password.showError(data.msg);
                        } else {
                            console.error(data);
                        }
                    
                    }
                }
            }, data);
        }
    },

    // Upload avatar
    avatar: {
        panel: null,
    }
}

let loginSteps = {
    // Enter credentials
    login: {
        panel: document.getElementById("auth-login"),
        subPanel: document.getElementById("auth-login-cred"),
        skip: document.getElementById("auth-login-forgot-go"),
        submit: function() {
            let { data, elements } = gatherInputs(loginSteps.login.subPanel);

            // No username entered
            if(data.username.length == 0) {
                elements.username.showError("Hello, who's this? Enter a username please!");
                return;
            }

            // No password entered
            if(data.password.length == 0) {
                elements.password.showError("Right, that's a great password. Now put something in the field please.");
                return;
            }

            AJAX.get("backend/api/user/user_login.php", {
                success: (data) => {
                    if(data.success){
                        console.log(data);
                    } else {
                        if(data.status == "username"){
                            elements.username.showError(data.msg);
                        } else if(data.status == "password") {
                            elements.password.showError(data.msg);
                        } else {
                            console.error(data);
                        }
                    }
                }
            }, data);
        }
    },

    // Forgot password
    forgot: {
        panel: document.getElementById("auth-login"),
        subPanel: document.getElementById("auth-login-forgot"),
        skip: document.getElementById("auth-login-back"),
        submit: function() {
            let { data, elements } = gatherInputs(loginSteps.forgot.subPanel);

            elements.email.showError("Sorry this feature is not available yet.");
            return;

            // Email regex validation
            if(!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test(data.email)) {
                elements.email.showError("This doesn't look like a valid email address to me. Please try another one!");
                return;
            }
        }
    }

};

// Needed to store data between account creation steps
let createData = {
    userId: null,
    email: null,
    code: null,
    username: null,
    password: null
}

// Link the create account steps together 
createSteps.email.next = createSteps.code;
createSteps.email.nextSkip = createSteps.codeAndEmail;

createSteps.code.next = createSteps.credentials;
createSteps.code.nextSkip = createSteps.email; // Technically it's going back

createSteps.codeAndEmail.next = createSteps.credentials;
createSteps.codeAndEmail.nextSkip = createSteps.email; // Going back

createSteps.credentials.next = createSteps.avatar;
createSteps.avatar.next = null;

// Link the login steps together
loginSteps.login.next = null;
loginSteps.login.nextSkip = loginSteps.forgot;

loginSteps.forgot.next = null;
loginSteps.forgot.nextSkip = loginSteps.login;

// For each step, add a submit event listener for create account and login
[createSteps, loginSteps].forEach(steps => {
    for (let step in steps) {

        // The button is in the subPanel, it there's none of that, it's in the panel
        let button;
        if(steps[step].subPanel != null) {
            button = steps[step].subPanel.querySelector("button");
        } else {
            if(steps[step].panel != null) {
                button = steps[step].panel.querySelector("button");
            } else {
                // Debug for now, should never happen
                console.log("No panel or subPanel for step " + step);
                continue;
            }
        }
        
        // Add the event listener, activate submit
        button.addEventListener("click", steps[step].submit);

        // If there is a goto button, add the event listener, and go there
        if(steps[step].skip != null) {
            steps[step].skip.addEventListener("click", () => {
                goTo(steps[step].nextSkip);
            });
        }
    }
});

// Keep track of the current page for both create and login
let currentStep = {
    "create": createSteps.email,
    "login": loginSteps.login
}

/**
 * Go to a step
 * @param {*} to
 */
function goTo(to){
    // Get previous step's page
    let current = null;
    if(currentPage == PAGES.create){
        current = "create";
    } else if (currentPage == PAGES.login) {
        current = "login";
    } else {
        return;
    }

    switchStep(currentStep[current], to);
    currentStep[current] = to;

    /**
     * Switches from one step to another (helper function)
     * @param {*} from 
     * @param {*} to 
     */
    function switchStep(from, to){
        const animDuration = 300;
        const animHold = 100;
    
        // What element to target for the change animation?
        let elFrom = from.panel != to.panel ? from.panel : from.subPanel;
        let elTo = from.panel != to.panel ? to.panel : to.subPanel;
    
        // If panel switch, change sub panel content to target
        if(from.panel != to.panel){
            // Hide any sub-panel form target
            to.panel.querySelectorAll(".auth-form").forEach(e => {
                e.classList.add("hidden");
            })
            // Show the target sub-panel
            to.subPanel.classList.remove("hidden");
        }
    
        // Hide the current panel
        elFrom.classList.add("fade-hide");
        elFrom.classList.remove("fade-show");
    
        // Prepare the to panel
        elTo.classList.add("fade-hide");
    
        setTimeout(() => {
            // Hide the current panel
            elFrom.classList.add("hidden");
            elFrom.classList.remove("fade-hide");
    
            // Show the new panel
            elTo.classList.remove("hidden");
    
            // Start animation with a bit of delay
            setTimeout(() => {
                elTo.classList.add("fade-show");
            }, animHold);
        }, animDuration);
    }
}
