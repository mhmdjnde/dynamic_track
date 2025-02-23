// error message hndler
function showError(errorId, message) {
  const errorEl = document.getElementById(errorId);
  if (errorEl) {
    errorEl.textContent = message;

    errorEl.style.color = "red";
    errorEl.style.fontSize = "14px";
    errorEl.style.marginTop = "-17px";
    errorEl.style.display = "block";
    errorEl.style.fontWeight = "bold";
    errorEl.style.fontFamily = "Arial, sans-serif";
  }
}

function clearError(errorId) {
  const errorEl = document.getElementById(errorId);
  if (errorEl) {
    errorEl.textContent = "";
    errorEl.style.color = "";
    errorEl.style.fontSize = "";
    errorEl.style.marginTop = "";
    errorEl.style.display = "";
    errorEl.style.fontWeight = "";
    errorEl.style.fontFamily = "";
  }
}

// STEPS ARRAY
const steps = [
  // 1) First Name
  {
    id: 1,
    question: "What is your first name?",
    subText:
      "We're excited to have you join us. <br>Tell us a bit about yourself to get started.",
    render: function (data) {
      return `
        <div class="input_container">
          <!-- Error container -->
          <div id="error_step_1" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="text"
            id="first_name"
            name="first_name"
            placeholder=" "
            value="${data.first_name || ""}"
          />
          <label for="first_name" class="placeholder_label">First name</label>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_1");
      const firstNameInput = document.getElementById("first_name");
      const firstName = firstNameInput.value.trim();

      if (!firstName) {
        showError("error_step_1", "First name cannot be empty.");
        return null;
      }
      return { first_name: firstName };
    },
  },

  // 2) Last Name
  {
    id: 2,
    question: "What is your last name?",
    subText: "We'll keep track of your last name as well.",
    render: function (data) {
      return `
        <div class="input_container">
          <div id="error_step_2" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="text"
            id="last_name"
            name="last_name"
            placeholder=" "
            value="${data.last_name || ""}"
          />
          <label for="last_name" class="placeholder_label">Last name</label>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_2");
      const lastNameInput = document.getElementById("last_name");
      const lastName = lastNameInput.value.trim();

      if (!lastName) {
        showError("error_step_2", "Last name cannot be empty.");
        return null;
      }
      return { last_name: lastName };
    },
  },

  // 3) Gender
  {
    id: 3,
    question: "What is your gender?",
    subText: "This helps us customize recommendations.",
    render: function (data) {
      const userGender = data.gender || "";
      const genderOptions = ["Male", "Female"];

      let html = `
        <div id="error_step_3" class="error_message" style="color:red; margin-bottom:8px;"></div>
        <div class="checkbox_container">
      `;
      genderOptions.forEach((option) => {
        const checked = userGender === option ? "checked" : "";
        html += `
          <label>
            <input type="radio" name="gender" value="${option}" ${checked}/>
            ${option}
          </label>
        `;
      });
      html += `</div>`;
      return html;
    },
    save: function () {
      clearError("error_step_3");
      const checkedRadio = document.querySelector(
        'input[name="gender"]:checked'
      );
      if (!checkedRadio) {
        showError("error_step_3", "Please select your gender.");
        return null;
      }
      return { gender: checkedRadio.value };
    },
  },

  // 4) Age
  {
    id: 4,
    question: "What is your age?",
    subText: "This helps us further tailor our recommendations.",
    render: function (data) {
      return `
        <div class="input_container">
          <div id="error_step_4" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="number"
            id="age"
            name="age"
            placeholder=" "
            value="${data.age || ""}"
          />
          <label for="age" class="placeholder_label">Age</label>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_4");
      const ageInput = document.getElementById("age");
      const ageValue = ageInput.value.trim();

      if (!ageValue) {
        showError("error_step_4", "Please enter your age.");
        return null;
      }
      const ageNum = parseInt(ageValue, 10);
      if (isNaN(ageNum) || ageNum <= 0) {
        showError("error_step_4", "Please enter a valid positive age.");
        return null;
      }
      return { age: ageNum };
    },
  },

  // 5) Weight
  {
    id: 5,
    question: "What is your weight (in kg)?",
    subText: "We need this to customize your fitness plan.",
    render: function (data) {
      return `
        <div class="input_container">
          <div id="error_step_5" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="number"
            step="0.1"
            id="weight"
            name="weight"
            placeholder=" "
            value="${data.weight || ""}"
          />
          <label for="weight" class="placeholder_label">Weight (kg)</label>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_5");
      const weightInput = document.getElementById("weight");
      const weight = weightInput.value.trim();

      if (!weight) {
        showError("error_step_5", "Weight cannot be empty.");
        return null;
      }
      const weightNum = parseFloat(weight);
      if (isNaN(weightNum) || weightNum <= 0) {
        showError("error_step_5", "Weight must be a positive number.");
        return null;
      }
      return { weight: weightNum };
    },
  },

  // 6) Height
  {
    id: 6,
    question: "What is your height (in cm)?",
    subText: "Example: 170 cm for 1.7 m.",
    render: function (data) {
      return `
        <div class="input_container">
          <div id="error_step_6" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="number"
            step="0.01"
            id="height"
            name="height"
            placeholder=" "
            value="${data.height || ""}"
          />
          <label for="height" class="placeholder_label">Height (cm)</label>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_6");
      const heightInput = document.getElementById("height");
      const height = heightInput.value.trim();

      if (!height) {
        showError("error_step_6", "Height cannot be empty.");
        return null;
      }
      const heightNum = parseFloat(height);
      if (isNaN(heightNum) || heightNum <= 0) {
        showError("error_step_6", "Height must be a positive number.");
        return null;
      }
      return { height: heightNum };
    },
  },

  // 7) Activity level
  {
    id: 7,
    question: "How would you describe your activity level?",
    subText:
      "Choose one that best fits you. This helps with calorie recommendations.",
    render: function (data) {
      const activityLevels = [
        "Sedentary",
        "Lightly active",
        "Moderately active",
        "Very active",
        "Extra active",
      ];
      let userActivity = data.activity || "";
      let html = `
        <div id="error_step_7" class="error_message" style="color:red; margin-bottom:8px;"></div>
        <div class="checkbox_container">
      `;
      activityLevels.forEach((level) => {
        const checked = userActivity === level ? "checked" : "";
        html += `
          <label>
            <input type="radio" name="activity" value="${level}" ${checked} />
            ${level}
          </label>
        `;
      });
      html += `</div>`;
      return html;
    },
    save: function () {
      clearError("error_step_7");
      const checkedRadio = document.querySelector(
        'input[name="activity"]:checked'
      );
      if (!checkedRadio) {
        showError("error_step_7", "Please select an activity level.");
        return null;
      }
      return { activity: checkedRadio.value };
    },
  },

  // 8) Goals
  {
    id: 8,
    question: (data) => `Thanks, ${data.first_name || ""}! Now for your goals.`,
    subText:
      "Pick exactly one <b>weight goal</b> and one <b>fitness/diet goal</b>.",
    render: function (data) {
      const weightGoals = ["Lose weight", "Maintain weight", "Gain weight"];
      const otherGoals = ["Gain muscle", "Modify my diet"];

      const selectedWeightGoal = data.goal_weight || "";
      const selectedOtherGoal = data.goal_other || "";

      let weightGoalsHTML = `<div style="margin-right: 20px;">
        <h3 style="margin-bottom: 5px;">Weight Goal</h3>`;
      weightGoals.forEach((wg) => {
        const checked = wg === selectedWeightGoal ? "checked" : "";
        weightGoalsHTML += `
          <label style="display: block; margin-bottom: 5px;">
            <input type="radio" name="goal_weight" value="${wg}" ${checked}/>
            ${wg}
          </label>
        `;
      });
      weightGoalsHTML += `</div>`;

      let otherGoalsHTML = `<div>
        <h3 style="margin-bottom: 5px;">Other Goal</h3>`;
      otherGoals.forEach((og) => {
        const checked = og === selectedOtherGoal ? "checked" : "";
        otherGoalsHTML += `
          <label style="display: block; margin-bottom: 5px;">
            <input type="radio" name="goal_other" value="${og}" ${checked}/>
            ${og}
          </label>
        `;
      });
      otherGoalsHTML += `</div>`;

      return `
        <div id="error_step_8" class="error_message" style="color:red; margin-bottom:8px;"></div>
        <div style="display: flex; flex-wrap: wrap;">
          ${weightGoalsHTML}
          ${otherGoalsHTML}
        </div>
      `;
    },
    save: function () {
      clearError("error_step_8");
      const weightGoalRadio = document.querySelector(
        'input[name="goal_weight"]:checked'
      );
      const otherGoalRadio = document.querySelector(
        'input[name="goal_other"]:checked'
      );

      if (!weightGoalRadio) {
        showError("error_step_8", "Please select exactly one weight goal.");
        return null;
      }
      if (!otherGoalRadio) {
        showError("error_step_8", "Please select exactly one other goal.");
        return null;
      }

      return {
        goal_weight: weightGoalRadio.value,
        goal_other: otherGoalRadio.value,
      };
    },
  },

  // 9) Email
  {
    id: 9,
    question: "What's your email address?",
    subText: "We'll use this email to create your account.",
    render: function (data) {
      return `
        <div class="input_container">
          <div id="error_step_9" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="email"
            id="email"
            name="email"
            placeholder=" "
            value="${data.email || ""}"
          />
          <label for="email" class="placeholder_label">Email</label>
        </div>
      `;
    },
    save: async function () {
      clearError("error_step_9");
      const emailInput = document.getElementById("email");
      const email = emailInput.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!email) {
        showError("error_step_9", "Email cannot be empty.");
        return null;
      }
      if (!emailRegex.test(email)) {
        showError("error_step_9", "Please enter a valid email address.");
        return null;
      }

      // Check if email is already registered via AJAX
      try {
        const response = await fetch("checkEmail.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ email }),
        });
        const result = await response.json();
        if (result.status === "error") {
          // Email already registered
          showError("error_step_9", result.message || "Email already in use.");
          return null;
        }
      } catch (err) {
        // If some server error or connectivity error
        showError(
          "error_step_9",
          "Unable to verify email at this time. Please try again."
        );
        return null;
      }

      return { email };
    },
  },

  // 10) Phone Number
  {
    id: 10,
    question: "What's your phone number?",
    subText: "Please provide your Lebanese phone number (+961).",
    render: function (data) {
      let phoneValue = data.phone_number || "";
      return `
        <div class="input_container" style="display: flex; gap: 10px;">
          <div id="error_step_10" class="error_message" style="color:red; position:absolute; top:-20px;"></div>
          <div style="display: flex; align-items: center; font-size: 1.2em;">
            🇱🇧 +961
          </div>
          <div style="flex: 1; position: relative;">
            <input
              class="input_box"
              type="tel"
              id="phone_number"
              name="phone_number"
              placeholder=" "
              value="${phoneValue}"
              style="width: 100%;"
            />
            <label for="phone_number" class="placeholder_label"
              >Phone number</label
            >
          </div>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_10");
      const phoneNumberEl = document.getElementById("phone_number");
      const phoneNumber = phoneNumberEl.value.trim();

      if (!phoneNumber) {
        showError("error_step_10", "Please enter your phone number.");
        return null;
      }
      if (!/^\d{8}$/.test(phoneNumber)) {
        showError(
          "error_step_10",
          "Phone number must be exactly 8 digits (numbers only)."
        );
        return null;
      }

      return {
        country_code: "+961",
        phone_number: phoneNumber,
      };
    },
  },

  // 11) Password
  {
    id: 11,
    question: "Choose a secure password",
    subText: "Make sure it's something only you know!",
    render: function (data) {
      return `
        <div class="input_container">
          <div id="error_step_11" class="error_message" style="color:red;"></div>

          <input
            class="input_box"
            type="password"
            id="password"
            name="password"
            placeholder=" "
            value="${data.password || ""}"
          />
          <label for="password" class="placeholder_label">Password</label>
        </div>
      `;
    },
    save: function () {
      clearError("error_step_11");
      const passwordInput = document.getElementById("password");
      const password = passwordInput.value;
      // At least 8 chars, 1 special, 1 digit, 1 uppercase
      const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d).{8,}$/;

      if (!password) {
        showError("error_step_11", "Password cannot be empty.");
        return null;
      }
      if (!passwordRegex.test(password)) {
        showError(
          "error_step_11",
          "Must be ≥8 chars, contain a special character, a digit, and an uppercase letter."
        );
        return null;
      }
      return { password };
    },
  },

  // 12) Summary
  {
    id: 12,
    question: (data) => `Great choices, ${data.first_name || ""}!`,
    subText: "Review your info before finishing up.",
    render: function (data) {
      const fullName = (data.first_name || "") + " " + (data.last_name || "");
      const gender = data.gender || "(not specified)";
      const age = data.age || "(not provided)";
      const weight = data.weight || "(not provided)";
      const height = data.height || "(not provided)";
      const activity = data.activity || "(not selected)";
      const weightGoal = data.goal_weight || "(not selected)";
      const otherGoal = data.goal_other || "(not selected)";
      const email = data.email || "(not provided)";
      const phoneNumber = data.phone_number
        ? `+961 ${data.phone_number}`
        : "(not provided)";

      return `
        <div id="error_step_12" class="error_message" style="color:red;"></div>

        <div style="text-align: center;">
          <p><strong>Name:</strong> ${fullName}</p>
          <p><strong>Gender:</strong> ${gender}</p>
          <p><strong>Age:</strong> ${age}</p>
          <p><strong>Weight (kg):</strong> ${weight}</p>
          <p><strong>Height (cm):</strong> ${height}</p>
          <p><strong>Activity:</strong> ${activity}</p>
          <p><strong>Weight Goal:</strong> ${weightGoal}</p>
          <p><strong>Other Goal:</strong> ${otherGoal}</p>
          <p><strong>Email:</strong> ${email}</p>
          <p><strong>Phone:</strong> ${phoneNumber}</p>
        </div>
      `;
    },
    save: function () {
      return {};
    },
  },
];

// ===========================================
// GLOBAL DATA AND LOGIC
// ===========================================
let userData = {};
let currentStep = 0;

const questionEl = document.getElementById("question");
const questionP = document.getElementById("question_p");
const stepContentEl = document.getElementById("step_content");
const backButton = document.getElementById("back_button");
const nextButton = document.getElementById("next_button");
const stepIndicator = document.getElementById("step_indicator");

window.addEventListener("DOMContentLoaded", () => {
  renderStep();
});

// Renders the current step
function renderStep() {
  const stepObj = steps[currentStep];

  // Update question text
  if (typeof stepObj.question === "function") {
    questionEl.innerHTML = stepObj.question(userData);
  } else {
    questionEl.innerHTML = stepObj.question;
  }

  questionP.innerHTML = stepObj.subText;

  stepContentEl.innerHTML = stepObj.render(userData);

  const progressPercent = ((currentStep + 1) / steps.length) * 100;
  stepIndicator.style.width = progressPercent + "%";

  // Show/hide the back button
  backButton.style.display = currentStep === 0 ? "none" : "inline-block";

  // If it's the last step, change Next button text to "Finish"
  nextButton.textContent = currentStep === steps.length - 1 ? "Finish" : "Next";
}

// "Next" button logic (async to allow email check)
async function goToNextStep() {
  const stepObj = steps[currentStep];
  let newData;
  if (stepObj.save.constructor.name === "AsyncFunction") {
    newData = await stepObj.save();
  } else {
    newData = stepObj.save();
  }

  // If the step returned null, there's a validation issue => stop
  if (newData === null) {
    return;
  }
  userData = { ...userData, ...newData };
  if (currentStep < steps.length - 1) {
    currentStep++;
    renderStep();
  } else {
    fetch("submit.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(userData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          console.log("Response from PHP:", data);
          window.location.href = "../primarypage/primary.php";
        } else {
          alert("Error occurred: " + (data.message || "Unknown server error."));
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error occurred while submitting the form.");
      });
  }
}
nextButton.addEventListener("click", goToNextStep);
backButton.addEventListener("click", () => {
  if (currentStep > 0) {
    currentStep--;
    renderStep();
  }
});
document.addEventListener("keydown", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();
    goToNextStep();
  }
});
