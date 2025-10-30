// Funcionalidad para la barra de navegación

document.addEventListener("DOMContentLoaded", () => {
  // Menú móvil
  const menuToggle = document.getElementById("menuToggle")
  const navMenu = document.getElementById("navMenu")

  if (menuToggle && navMenu) {
    menuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("active")

      // Cambiar el icono del botón
      const icon = menuToggle.querySelector("i")
      if (icon.classList.contains("fa-bars")) {
        icon.classList.remove("fa-bars")
        icon.classList.add("fa-times")
      } else {
        icon.classList.remove("fa-times")
        icon.classList.add("fa-bars")
      }
    })
  }

  // Año actual en el footer
  const currentYearElements = document.querySelectorAll("#currentYear")
  const currentYear = new Date().getFullYear()

  currentYearElements.forEach((element) => {
    element.textContent = currentYear
  })
})
