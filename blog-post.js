// Funcionalidad para la página de artículo del blog

document.addEventListener("DOMContentLoaded", () => {
  // Botón de compartir
  const shareBtn = document.getElementById("shareBtn")
  const favoriteBtn = document.getElementById("favoriteBtn")

  shareBtn.addEventListener("click", () => {
    if (navigator.share) {
      navigator.share({
        title: document.title,
        text: "Mira este interesante artículo sobre el cuidado de mascotas",
        url: window.location.href,
      })
    } else {
      // Fallback para navegadores que no soportan Web Share API
      navigator.clipboard.writeText(window.location.href).then(() => {
        alert("Enlace copiado al portapapeles")
      })
    }
  })

  // Botón de favoritos
  favoriteBtn.addEventListener("click", () => {
    const icon = favoriteBtn.querySelector("i")
    icon.classList.toggle("far")
    icon.classList.toggle("fas")

    if (icon.classList.contains("fas")) {
      favoriteBtn.style.color = "#ef4444"
      console.log("Artículo guardado en favoritos")
    } else {
      favoriteBtn.style.color = ""
      console.log("Artículo quitado de favoritos")
    }
  })

  // Newsletter
  const newsletterForm = document.querySelector(".newsletter-form")
  newsletterForm.addEventListener("submit", (e) => {
    e.preventDefault()
    const email = newsletterForm.querySelector('input[type="email"]').value
    if (email) {
      alert("¡Gracias por suscribirte! Te enviaremos nuestros mejores consejos a: " + email)
      newsletterForm.reset()
    }
  })

  // Cargar contenido dinámico basado en el ID del artículo
  const urlParams = new URLSearchParams(window.location.search)
  const postId = urlParams.get("id")

  if (postId) {
    loadPostData(postId)
  }
})

// Función para cargar datos del artículo
function loadPostData(postId) {
  // Datos de ejemplo - en una implementación real vendrían de una API
  const postsData = {
    1: {
      title: "10 consejos para cuidar a tu mascota en verano",
      category: "Consejos",
      author: "María González",
      date: "10 de Mayo, 2025",
      image: "https://placeimg.com/800/400/animals?id=1",
      content: "Contenido del artículo sobre cuidados de verano...",
    },
    2: {
      title: "La historia de Toby: de la calle a un hogar amoroso",
      category: "Historias",
      author: "Carlos Rodríguez",
      date: "5 de Mayo, 2025",
      image: "https://placeimg.com/800/400/animals?id=2",
      content: "Historia inspiradora de adopción...",
    },
    3: {
      title: "Alimentación adecuada para gatos: mitos y realidades",
      category: "Alimentación",
      author: "Laura Martínez",
      date: "1 de Mayo, 2025",
      image: "https://placeimg.com/800/400/animals?id=3",
      content: "Guía completa sobre alimentación felina...",
    },
    4: {
      title: "Cómo entrenar a tu perro con refuerzo positivo",
      category: "Entrenamiento",
      author: "Pedro Sánchez",
      date: "28 de Abril, 2025",
      image: "https://placeimg.com/800/400/animals?id=4",
      content: "Técnicas de entrenamiento efectivas...",
    },
  }

  const postData = postsData[postId]
  if (postData) {
    // Actualizar título de la página
    document.title = `${postData.title} - Adopt Me`

    // Actualizar contenido de la página
    document.querySelector(".post-title").textContent = postData.title
    document.querySelector(".post-category").textContent = postData.category
    document.querySelector(".author-name").textContent = postData.author
    document.querySelector(".post-date").textContent = postData.date
    document.querySelector(".post-image img").src = postData.image
    document.querySelector(".post-image img").alt = postData.title

    // Actualizar breadcrumb
    const breadcrumbSpan = document.querySelector(".breadcrumb span:last-child")
    breadcrumbSpan.textContent = postData.title
  }
}
