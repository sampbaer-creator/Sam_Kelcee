const menuButton = document.getElementById("menu-btn");
const mobileMenu = document.getElementById("mobile-menu");
const menuOverlay = document.getElementById("menu-overlay");

function setMenuOpen(isOpen) {
  document.body.classList.toggle("menu-open", isOpen);
  if (menuButton) {
    menuButton.setAttribute("aria-expanded", String(isOpen));
  }
}

if (menuButton && mobileMenu && menuOverlay) {
  menuButton.addEventListener("click", () => {
    setMenuOpen(!document.body.classList.contains("menu-open"));
  });

  menuOverlay.addEventListener("click", () => setMenuOpen(false));

  mobileMenu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => setMenuOpen(false));
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      setMenuOpen(false);
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".fade-in").forEach((element) => {
    element.classList.add("is-visible");
  });
});
