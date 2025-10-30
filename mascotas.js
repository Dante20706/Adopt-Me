// Funcionalidad para la página de mascotas disponibles

document.addEventListener("DOMContentLoaded", () => {
  // Elementos del DOM
  const petTypeFilter = document.getElementById("petTypeFilter")
  const ageFilter = document.getElementById("ageFilter")
  const sizeFilter = document.getElementById("sizeFilter")
  const locationFilter = document.getElementById("locationFilter")
  const petsGrid = document.getElementById("petsGrid")
  const loadMoreBtn = document.getElementById("loadMore")

  // Funcionalidad de filtros
  function filterPets() {
    const petCards = document.querySelectorAll(".pet-card")
    const typeValue = petTypeFilter.value
    const ageValue = ageFilter.value
    const sizeValue = sizeFilter.value
    const locationValue = locationFilter.value

    petCards.forEach((card) => {
      const petType = card.getAttribute("data-type")
      const petAge = card.getAttribute("data-age")
      const petSize = card.getAttribute("data-size")
      const petLocation = card.getAttribute("data-location")

      const typeMatch = typeValue === "all" || petType === typeValue
      const ageMatch = ageValue === "all" || petAge === ageValue
      const sizeMatch = sizeValue === "all" || petSize === sizeValue
      const locationMatch = locationValue === "all" || petLocation === locationValue

      if (typeMatch && ageMatch && sizeMatch && locationMatch) {
        card.classList.remove("hidden")
      } else {
        card.classList.add("hidden")
      }
    })

    // Actualizar contador de resultados
    updateResultsCount()
  }

  // Actualizar contador de resultados
  function updateResultsCount() {
    const visibleCards = document.querySelectorAll(".pet-card:not(.hidden)")
    const totalCards = document.querySelectorAll(".pet-card")

    // Aquí podrías agregar un elemento para mostrar el contador
    console.log(`Mostrando ${visibleCards.length} de ${totalCards.length} mascotas`)
  }

  // Event listeners para filtros
  petTypeFilter.addEventListener("change", filterPets)
  ageFilter.addEventListener("change", filterPets)
  sizeFilter.addEventListener("change", filterPets)
  locationFilter.addEventListener("change", filterPets)

  // Inicializar filtros
  filterPets()
})
