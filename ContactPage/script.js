// Toggle mobile menu
function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  navLinks.classList.toggle("active");
}

// Simple client-side checks
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  contactForm.addEventListener("submit", function (event) {
    const firstName = document.getElementById("firstName");
    const lastName = document.getElementById("lastName");
    const email = document.getElementById("email");
    const subject = document.getElementById("subject");
    const message = document.getElementById("message");

    if (
      !firstName.value.trim() ||
      !lastName.value.trim() ||
      !email.value.trim() ||
      !subject.value.trim() ||
      !message.value.trim()
    ) {
      event.preventDefault();

      contactForm.classList.remove("shake");
      void contactForm.offsetWidth; // reflow
      contactForm.classList.add("shake");

      alert("Please fill out all required fields.");
      return;
    }

    // Basic email format check
    if (!validateEmail(email.value)) {
      event.preventDefault();

      contactForm.classList.remove("shake");
      void contactForm.offsetWidth; // reflow
      contactForm.classList.add("shake");

      alert("Please enter a valid email address.");
      return;
    }
  });
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}
