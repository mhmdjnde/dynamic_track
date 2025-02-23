// Q&A TOGGLE
document.querySelectorAll(".qa-question").forEach((button) => {
  button.addEventListener("click", () => {
    const answer = button.nextElementSibling;
    const arrow = button.querySelector(".qa-arrow");

    // Toggle active classes
    answer.classList.toggle("active");
    arrow.classList.toggle("active");

    // Close other answers
    document.querySelectorAll(".qa-answer").forEach((item) => {
      if (item !== answer) {
        item.classList.remove("active");
        item.previousElementSibling
          .querySelector(".qa-arrow")
          .classList.remove("active");
      }
    });
  });
});

// SCROLL SPY + SMOOTH SCROLL
const navLinks = document.querySelectorAll(".nav-item a");
const sections = [
  document.getElementById("section-home"),
  document.getElementById("section-features"),
  document.getElementById("section-qa"),
];

// Smooth scrolling when clicking nav links
navLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    e.preventDefault();
    const targetId = link.getAttribute("href");
    const targetElement = document.querySelector(targetId);

    if (targetElement) {
      targetElement.scrollIntoView({
        behavior: "smooth",
      });
    }
  });
});

window.addEventListener("scroll", () => {
  let currentSectionId = "";

  sections.forEach((section) => {
    const sectionTop = section.offsetTop - 80;
    const sectionHeight = section.offsetHeight;

    if (
      window.pageYOffset >= sectionTop &&
      window.pageYOffset < sectionTop + sectionHeight
    ) {
      currentSectionId = section.getAttribute("id");
    }
  });

  navLinks.forEach((link) => {
    link.classList.remove("active-link");
    if (link.getAttribute("href") === `#${currentSectionId}`) {
      link.classList.add("active-link");
    }
  });
});
