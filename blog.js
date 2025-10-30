// Funcionalidad para la página del blog

const blogPosts = [
  {
    image: "image1.jpg",
    category: "Category 1",
    title: "Title 1",
    excerpt: "Excerpt 1",
    date: "Date 1",
    author: "Author 1",
  },
  {
    image: "image2.jpg",
    category: "Category 2",
    title: "Title 2",
    excerpt: "Excerpt 2",
    date: "Date 2",
    author: "Author 2",
  },
  // Add more posts as needed
]

document.addEventListener("DOMContentLoaded", () => {
  // Generar tarjetas de blog
  const postsGrid = document.getElementById("postsGrid")

  if (postsGrid && typeof blogPosts !== "undefined") {
    // Mostrar solo los primeros 6 artículos
    const postsToShow = blogPosts.slice(0, 6)

    postsToShow.forEach((post) => {
      const postCard = createPostCard(post)
      postsGrid.appendChild(postCard)
    })
  }

  // Función para crear una tarjeta de blog
  function createPostCard(post) {
    const card = document.createElement("div")
    card.className = "post-card"

    card.innerHTML = `
            <div class="post-image">
                <img src="${post.image}" alt="${post.title}">
            </div>
            <div class="post-content">
                <span class="post-category">${post.category}</span>
                <h3 class="post-title">${post.title}</h3>
                <p class="post-excerpt">${post.excerpt}</p>
                <div class="post-info">
                    <span class="post-date">
                        <i class="far fa-calendar"></i> ${post.date}
                    </span>
                    <span class="post-author">
                        <i class="far fa-user"></i> ${post.author}
                    </span>
                </div>
            </div>
            <div class="post-footer">
                <a href="#" class="read-more">Leer artículo completo</a>
            </div>
        `

    return card
  }

  // Funcionalidad para el formulario de newsletter
  const newsletterForm = document.querySelector(".newsletter-form")

  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (e) {
      e.preventDefault()
      const emailInput = this.querySelector('input[type="email"]')
      const email = emailInput.value.trim()

      if (email) {
        alert(`¡Gracias por suscribirte con el email: ${email}!`)
        emailInput.value = ""
      } else {
        alert("Por favor, introduce un email válido.")
      }
    })
  }

  // Funcionalidad para el botón de cargar más
  const loadMoreBtn = document.querySelector(".load-more button")

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", () => {
      alert("En una implementación real, aquí se cargarían más artículos.")
    })
  }
})
