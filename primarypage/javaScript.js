document.addEventListener("DOMContentLoaded", function () {
  const foodLink = document.getElementById("foodLink");
  foodLink.addEventListener("click", (e) => {
    e.preventDefault();
    document
      .querySelector(".meal-windows-container")
      .scrollIntoView({ behavior: "smooth" });
  });
  const hamburger = document.querySelector(".hamburger");
  const navLinks = document.querySelector(".nav-links");
  if (hamburger && navLinks) {
    hamburger.addEventListener("click", () => {
      navLinks.classList.toggle("active");
    });
  }

  let state = {
    totalCalsConsumed: parseInt(savedState.totalCalsConsumed) || 0,
    totalCarbs: savedState.totalCarbs || 0,
    totalProtein: savedState.totalProtein || 0,
    totalFat: savedState.totalFat || 0,
    totalSugar: savedState.totalSugar || 0,
    totalWaterIntake: parseInt(savedState.totalWaterIntake) || 0,

    mealEntries: savedState.mealEntries || {
      breakfast: [],
      lunch: [],
      dinner: [],
      snack: [],
    },
  };

  const cookieUserEmail = decodeURIComponent(userEmail);

  // ======== (NEW) 2.5) Reset cookie if user is different ========
  // We'll keep track of which user was the "lastUser" in a separate cookie.
  const lastUser = Cookies.get("lastUser");
  if (!lastUser || lastUser !== cookieUserEmail) {
    const newCookieName = "dailyCalories_" + cookieUserEmail;
    Cookies.set(newCookieName, JSON.stringify({}), { expires: 7 });

    // Update the lastUser cookie to current user
    Cookies.set("lastUser", cookieUserEmail, { expires: 7 });
  }

  // ======== 3) Helper to POST updated state to server (stores in DB) ========
  async function saveStateToServer() {
    try {
      await fetch("primary.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(state),
      });
    } catch (err) {
      console.error("Error saving state to server:", err);
    }
  }

  // ======== 4) WATER TRACKER ========
  let totalWaterIntake = parseInt(state.totalWaterIntake) || 0;
  const waterMax = 4000; // mL
  const fillElement = document.querySelector(".water-progress-fill");
  const currentWaterText = document.getElementById("currentWaterText");
  const addWaterBtn = document.getElementById("addWaterBtn");

  function updateWaterTracker() {
    let fillPercentage = (totalWaterIntake / waterMax) * 100;
    if (fillPercentage > 100) fillPercentage = 100;
    fillElement.style.width = fillPercentage + "%";
    fillElement.style.backgroundColor =
      totalWaterIntake > waterMax ? "red" : "green";
    // We keep the display for water in liters with 2 decimals
    currentWaterText.innerText = (totalWaterIntake / 1000).toFixed(2) + "L";
  }

  if (addWaterBtn) {
    addWaterBtn.addEventListener("click", () => {
      const waterInput = document.getElementById("waterInput");
      const amount = parseInt(waterInput.value);
      if (!isNaN(amount) && amount > 0) {
        totalWaterIntake += amount;
        state.totalWaterIntake = totalWaterIntake;
        updateWaterTracker();
        saveStateToServer();
        waterInput.value = "";
      }
    });
  }

  // ======== 5) 7-DAY CALORIE SUMMARY ========
  const summaryDaysContainer = document.getElementById("summaryDays");

  function getCurrentDate() {
    const today = new Date();
    return today.toISOString().split("T")[0];
  }

  function getLast7Days() {
    const today = new Date();
    const last7Days = [];
    for (let i = 6; i >= 0; i--) {
      // from 6 days ago up to today
      const d = new Date(today);
      d.setDate(today.getDate() - i);
      last7Days.push(d.toISOString().split("T")[0]);
    }
    return last7Days;
  }

  // Use a user-specific cookie name
  function getCookieName() {
    return "dailyCalories_" + cookieUserEmail;
  }

  // Parse dailyCalories to ensure they're stored/returned as integers
  function getLast7DaysCalories() {
    const cookieName = getCookieName();
    const dailyCalories = Cookies.get(cookieName)
      ? JSON.parse(Cookies.get(cookieName))
      : {};
    const last7Days = getLast7Days();
    return last7Days.map((date) => parseInt(dailyCalories[date]) || 0);
  }

  function saveDailyCalories(calories) {
    const currentDate = getCurrentDate();
    const cookieName = getCookieName();
    const dailyCalories = Cookies.get(cookieName)
      ? JSON.parse(Cookies.get(cookieName))
      : {};
    dailyCalories[currentDate] = parseInt(calories) || 0;
    // Expires in 7 days, user-specific cookie:
    Cookies.set(cookieName, JSON.stringify(dailyCalories), { expires: 7 });
  }

  function update7DaySummary() {
    const last7DaysDates = getLast7Days();
    const dailyIntakes = getLast7DaysCalories();

    summaryDaysContainer.innerHTML = "";
    last7DaysDates.forEach((dateStr, index) => {
      const dayDiv = document.createElement("div");
      dayDiv.classList.add("summary-day");

      // Derive day name from date
      const dateObj = new Date(dateStr);
      const dayOfWeek = dateObj.getDay(); // 0=Sunday..6=Saturday
      const dayNames = [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
      ];
      const dayName = dayNames[dayOfWeek];

      const dayLabel = document.createElement("div");
      dayLabel.classList.add("day-label");
      dayLabel.textContent = dayName;

      const dayCalories = document.createElement("div");
      dayCalories.classList.add("day-calories");

      const intake = dailyIntakes[index];
      if (intake !== undefined && !isNaN(intake)) {
        dayCalories.textContent = `${intake} / ${dailyGoal} kcal`;
        if (intake === 0) {
          dayDiv.classList.add("gray");
        } else if (intake > dailyGoal) {
          dayDiv.classList.add("red");
        } else {
          dayDiv.classList.add("green");
        }
      } else {
        dayCalories.textContent = "No data";
        dayDiv.classList.add("gray");
      }

      dayDiv.appendChild(dayLabel);
      dayDiv.appendChild(dayCalories);
      summaryDaysContainer.appendChild(dayDiv);
    });
  }

  // ======== 6) FOOD & MEAL ENTRIES ========
  const mealTotals = {
    breakfast: 0,
    lunch: 0,
    dinner: 0,
    snack: 0,
  };

  // DOM references for macros (these remain floats in display):
  const carbsStat = document
    .querySelectorAll(".side-stats .stat-item")[0]
    .querySelector(".stat-number");
  const proteinStat = document
    .querySelectorAll(".side-stats .stat-item")[1]
    .querySelector(".stat-number");
  const fatStat = document
    .querySelectorAll(".side-stats .stat-item")[2]
    .querySelector(".stat-number");
  const sugarStat = document
    .querySelectorAll(".side-stats .stat-item")[3]
    .querySelector(".stat-number");

  // DOM references for meal totals (integers):
  const breakfastStat = document
    .querySelectorAll(".side-stats")[1]
    .querySelectorAll(".stat-number")[0];
  const lunchStat = document
    .querySelectorAll(".side-stats")[1]
    .querySelectorAll(".stat-number")[1];
  const dinnerStat = document
    .querySelectorAll(".side-stats")[1]
    .querySelectorAll(".stat-number")[2];
  const snackStat = document
    .querySelectorAll(".side-stats")[1]
    .querySelectorAll(".stat-number")[3];

  const breakfastTotalElem = document.querySelector(
    '.meal-box[data-meal="breakfast"] .meal-total-cals'
  );
  const lunchTotalElem = document.querySelector(
    '.meal-box[data-meal="lunch"] .meal-total-cals'
  );
  const dinnerTotalElem = document.querySelector(
    '.meal-box[data-meal="dinner"] .meal-total-cals'
  );
  const snackTotalElem = document.querySelector(
    '.meal-box[data-meal="snack"] .meal-total-cals'
  );

  const appleCircle = document.querySelector(".apple-circle");
  const caloriesLeftSpan = document.querySelector(".calories-left");
  const leftoverElem = document.querySelector(".budget-number");

  // ======== 7) Quantity Modal ========
  const quantityModal = document.getElementById("quantityModal");
  const closeQuantityModalBtn = document.getElementById("closeQuantityModal");
  const quantityInput = document.getElementById("quantityInput");
  const confirmQuantityBtn = document.getElementById("confirmQuantityBtn");

  let selectedFoodItem = null;
  let selectedMealType = null;

  function showQuantityModal(foodItem, mealType) {
    selectedFoodItem = foodItem;
    selectedMealType = mealType;
    quantityModal.style.display = "block";
    quantityInput.value = "";
    quantityInput.focus();
  }

  function hideQuantityModal() {
    quantityModal.style.display = "none";
    selectedFoodItem = null;
    selectedMealType = null;
  }

  if (closeQuantityModalBtn) {
    closeQuantityModalBtn.addEventListener("click", hideQuantityModal);
  }
  window.addEventListener("click", (event) => {
    if (event.target === quantityModal) hideQuantityModal();
  });

  if (confirmQuantityBtn) {
    confirmQuantityBtn.addEventListener("click", function () {
      const gramsInput = parseFloat(quantityInput.value);
      if (isNaN(gramsInput) || gramsInput <= 0) return;

      // We'll store grams as integer
      const grams = parseInt(gramsInput);

      // Macros remain float-based calculations, but calories become int
      const multiplier = grams / 100;
      const adjustedFood = {
        name: selectedFoodItem.name,
        grams: grams,
        calories: parseInt(selectedFoodItem.calories * multiplier),
        fat: selectedFoodItem.fat * multiplier,
        carbs: selectedFoodItem.carbs * multiplier,
        protein: selectedFoodItem.protein * multiplier,
        sugar: selectedFoodItem.sugar * multiplier,
      };

      addFoodToMeal(adjustedFood, selectedMealType);
      hideQuantityModal();
    });
  }

  // ======== 8) Search Modal ========
  let activeMealType = null;
  const searchModal = document.getElementById("searchModal");
  const closeSearchModalBtn = document.getElementById("closeSearchModal");
  const foodSearchInput = document.getElementById("foodSearchInput");
  const searchResultsContainer = document.getElementById("searchResults");

  function showSearchModal(mealType) {
    activeMealType = mealType;
    searchModal.style.display = "block";
    if (foodSearchInput) {
      foodSearchInput.value = "";
      foodSearchInput.focus();
    }
    if (searchResultsContainer) {
      searchResultsContainer.innerHTML = "";
    }
  }

  function hideSearchModal() {
    searchModal.style.display = "none";
  }

  if (closeSearchModalBtn) {
    closeSearchModalBtn.addEventListener("click", hideSearchModal);
  }
  window.addEventListener("click", (event) => {
    if (event.target === searchModal) hideSearchModal();
  });

  // "Add Food" buttons in each meal box
  document.querySelectorAll(".add-food-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const mealBox = e.target.closest(".meal-box");
      if (!mealBox) return;
      const mealType = mealBox.dataset.meal;
      showSearchModal(mealType);
    });
  });

  let currentSearchMatches = [];

  if (foodSearchInput) {
    foodSearchInput.addEventListener("input", function () {
      const query = this.value.trim().toLowerCase();
      searchResultsContainer.innerHTML = "";
      if (!query) return;

      currentSearchMatches = foodsDatabase.filter((food) =>
        food.name.toLowerCase().includes(query)
      );

      // Render matched items
      currentSearchMatches.forEach((foodItem, index) => {
        const cardDiv = document.createElement("div");
        cardDiv.classList.add("search-result-card");
        cardDiv.innerHTML = `
          <div class="search-result-header">
            <span class="food-name-result">${foodItem.name}</span>
            <button class="add-to-meal-btn" data-index="${index}">Add to Meal</button>
          </div>
          <div class="nutrition-line">
            <span>Cal: ${parseInt(foodItem.calories)} </span>
            <span>Fat: ${foodItem.fat}g</span>
            <span>Carbs: ${foodItem.carbs}g</span>
            <span>Protein: ${foodItem.protein}g</span>
            <span>Sugar: ${foodItem.sugar}g</span>
          </div>
        `;
        searchResultsContainer.appendChild(cardDiv);
      });
    });
  }

  if (searchResultsContainer) {
    searchResultsContainer.addEventListener("click", (event) => {
      if (event.target.classList.contains("add-to-meal-btn")) {
        const index = parseInt(event.target.dataset.index);
        const foodItem = currentSearchMatches[index];
        hideSearchModal();
        if (foodItem && activeMealType) {
          showQuantityModal(foodItem, activeMealType);
        }
      }
    });
  }

  // ======== 9) Render meal entries from `state.mealEntries` ========
  function renderAllMeals() {
    Object.keys(state.mealEntries).forEach((mealType) => {
      const mealBox = document.querySelector(
        `.meal-box[data-meal="${mealType}"]`
      );
      if (!mealBox) return;

      const entriesContainer = mealBox.querySelector(".meal-entries");
      entriesContainer.innerHTML = ""; // clear existing

      state.mealEntries[mealType].forEach((item) => {
        const entryDiv = document.createElement("div");
        entryDiv.classList.add("food-entry");
        entryDiv.innerHTML = `
          <div class="food-line">
            <span class="food-name">${item.name} (${parseInt(
          item.grams
        )}g)</span>
            <span class="food-cals">${parseInt(item.calories)} kcal</span>
          </div>
          <div class="food-line">
            <span>Fat: ${item.fat.toFixed(1)}g</span>
            <span>Carbs: ${item.carbs.toFixed(1)}g</span>
            <span>Protein: ${item.protein.toFixed(1)}g</span>
            <span>Sugar: ${item.sugar.toFixed(1)}g</span>
            <button class="remove-food-btn"
              data-name="${item.name}"
              data-grams="${item.grams}"
              data-calories="${item.calories}"
              data-fat="${item.fat}"
              data-carbs="${item.carbs}"
              data-protein="${item.protein}"
              data-sugar="${item.sugar}"
              data-meal-type="${mealType}">
              Remove
            </button>
          </div>
        `;
        const removeBtn = entryDiv.querySelector(".remove-food-btn");
        removeBtn.addEventListener("click", function () {
          const mealType = this.dataset.mealType;
          removeFoodFromMeal(
            {
              name: this.dataset.name,
              grams: parseInt(this.dataset.grams),
              calories: parseInt(this.dataset.calories),
              fat: parseFloat(this.dataset.fat),
              carbs: parseFloat(this.dataset.carbs),
              protein: parseFloat(this.dataset.protein),
              sugar: parseFloat(this.dataset.sugar),
            },
            mealType
          );
        });

        entriesContainer.appendChild(entryDiv);
      });
    });
  }

  // ======== 10) Add/Remove Foods ========
  function addFoodToMeal(foodItem, mealType) {
    foodItem.calories = parseInt(foodItem.calories);
    foodItem.grams = parseInt(foodItem.grams);

    state.mealEntries[mealType].push(foodItem);
    updateUI();
    saveStateToServer();
  }

  function removeFoodFromMeal(foodItem, mealType) {
    const arr = state.mealEntries[mealType];
    const idx = arr.findIndex(
      (x) =>
        x.name === foodItem.name &&
        x.grams === foodItem.grams &&
        x.calories === foodItem.calories
    );
    if (idx > -1) {
      arr.splice(idx, 1);
    }
    updateUI();
    saveStateToServer();
  }

  // ======== 11) Recompute aggregator (macros/cals) and update UI ========
  function updateUI() {
    let totalCarbs = 0;
    let totalProtein = 0;
    let totalFat = 0;
    let totalSugar = 0;
    let totalCalsConsumed = 0;

    mealTotals.breakfast = 0;
    mealTotals.lunch = 0;
    mealTotals.dinner = 0;
    mealTotals.snack = 0;

    // Sum up macros/cals from all meal entries
    Object.keys(state.mealEntries).forEach((mealType) => {
      const mealArray = state.mealEntries[mealType] || [];
      mealArray.forEach((item) => {
        totalCarbs += item.carbs;
        totalProtein += item.protein;
        totalFat += item.fat;
        totalSugar += item.sugar;

        const cals = parseInt(item.calories) || 0;
        totalCalsConsumed += cals;
        mealTotals[mealType] += cals;
      });
    });

    // Update state
    state.totalCarbs = totalCarbs;
    state.totalProtein = totalProtein;
    state.totalFat = totalFat;
    state.totalSugar = totalSugar;
    state.totalCalsConsumed = totalCalsConsumed;

    // Update macro UI (floats)
    carbsStat.textContent = `${totalCarbs.toFixed(1)} of ${macroCarbs}g`;
    proteinStat.textContent = `${totalProtein.toFixed(1)} of ${macroProtein}g`;
    fatStat.textContent = `${totalFat.toFixed(1)} of ${macroFat}g`;
    sugarStat.textContent = `${totalSugar.toFixed(1)} of ${macroSugar}g`;

    // Update meal totals (integers)
    breakfastStat.textContent = mealTotals.breakfast;
    lunchStat.textContent = mealTotals.lunch;
    dinnerStat.textContent = mealTotals.dinner;
    snackStat.textContent = mealTotals.snack;

    // Update meal box headers (integers)
    breakfastTotalElem.textContent = `${mealTotals.breakfast} kcal`;
    lunchTotalElem.textContent = `${mealTotals.lunch} kcal`;
    dinnerTotalElem.textContent = `${mealTotals.dinner} kcal`;
    snackTotalElem.textContent = `${mealTotals.snack} kcal`;

    // Update leftover cals (integer)
    const leftover = Math.max(0, dailyGoal - totalCalsConsumed);
    leftoverElem.textContent = leftover.toString();

    // Update the apple circle
    const percent = Math.min((totalCalsConsumed / dailyGoal) * 100, 100);
    caloriesLeftSpan.textContent = `${Math.round(percent)}%`;
    caloriesLeftSpan.style.color = percent >= 100 ? "red" : "green";
    appleCircle.style.background = `
      conic-gradient(#03d10a 0% ${percent}%, transparent ${percent}% 100%)
    `;

    // Save daily cals to user-specific cookie
    saveDailyCalories(totalCalsConsumed);

    // Re-render meals & 7-day summary
    renderAllMeals();
    update7DaySummary();
  }

  // ======== 12) Initial rendering ========
  updateUI();
  updateWaterTracker();
  update7DaySummary();
});
