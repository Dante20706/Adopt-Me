// JavaScript del frontend para manejar la newsletter

class NewsletterManager {
  constructor() {
    this.apiUrl = "http://localhost:3000/api/newsletter" // Ajusta según tu configuración
    this.init()
  }

  init() {
    // Manejar formulario de newsletter
    const newsletterForm = document.querySelector(".newsletter-form")
    if (newsletterForm) {
      newsletterForm.addEventListener("submit", (e) => this.handleSubscription(e))
    }

    // Manejar formularios de newsletter en otras páginas
    const newsletterForms = document.querySelectorAll("[data-newsletter-form]")
    newsletterForms.forEach((form) => {
      form.addEventListener("submit", (e) => this.handleSubscription(e))
    })
  }

  async handleSubscription(event) {
    event.preventDefault()

    const form = event.target
    const emailInput = form.querySelector('input[type="email"]')
    const nameInput = form.querySelector('input[name="name"]')
    const submitButton = form.querySelector('button[type="submit"]')

    const email = emailInput.value.trim()
    const name = nameInput ? nameInput.value.trim() : ""

    // Validación básica
    if (!this.isValidEmail(email)) {
      this.showMessage("Por favor, introduce un email válido", "error")
      return
    }

    // Deshabilitar botón durante el envío
    const originalText = submitButton.textContent
    submitButton.disabled = true
    submitButton.textContent = "Suscribiendo..."

    try {
      const response = await fetch(`${this.apiUrl}/subscribe`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, name }),
      })

      const data = await response.json()

      if (data.success) {
        this.showMessage(data.message, "success")
        form.reset()

        // Opcional: Mostrar modal de confirmación
        this.showSubscriptionModal()
      } else {
        this.showMessage(data.message, "error")
      }
    } catch (error) {
      console.error("Error en suscripción:", error)
      this.showMessage("Error de conexión. Por favor, intenta de nuevo.", "error")
    } finally {
      // Rehabilitar botón
      submitButton.disabled = false
      submitButton.textContent = originalText
    }
  }

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  showMessage(message, type = "info") {
    // Crear elemento de mensaje
    const messageDiv = document.createElement("div")
    messageDiv.className = `newsletter-message newsletter-message--${type}`
    messageDiv.textContent = message

    // Estilos para el mensaje
    messageDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 5px;
      color: white;
      font-weight: 500;
      z-index: 10000;
      max-width: 400px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    `

    // Colores según el tipo
    switch (type) {
      case "success":
        messageDiv.style.backgroundColor = "#22c55e"
        break
      case "error":
        messageDiv.style.backgroundColor = "#ef4444"
        break
      default:
        messageDiv.style.backgroundColor = "#3b82f6"
    }

    // Añadir al DOM
    document.body.appendChild(messageDiv)

    // Remover después de 5 segundos
    setTimeout(() => {
      messageDiv.style.opacity = "0"
      messageDiv.style.transform = "translateX(100%)"
      setTimeout(() => {
        if (messageDiv.parentNode) {
          messageDiv.parentNode.removeChild(messageDiv)
        }
      }, 300)
    }, 5000)
  }

  showSubscriptionModal() {
    // Crear modal de confirmación
    const modal = document.createElement("div")
    modal.className = "subscription-modal"
    modal.innerHTML = `
      <div class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h3>🎉 ¡Suscripción exitosa!</h3>
            <button class="modal-close">&times;</button>
          </div>
          <div class="modal-body">
            <p>Gracias por suscribirte a nuestro newsletter.</p>
            <p>Hemos enviado un email de confirmación a tu bandeja de entrada.</p>
            <p>¡Prepárate para recibir contenido increíble sobre mascotas!</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary modal-close">¡Perfecto!</button>
          </div>
        </div>
      </div>
    `

    // Estilos del modal
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 10001;
    `

    // Añadir estilos CSS
    const style = document.createElement("style")
    style.textContent = `
      .modal-overlay {
        background: rgba(0, 0, 0, 0.5);
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
      }
      .modal-content {
        background: white;
        border-radius: 10px;
        max-width: 500px;
        width: 100%;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: modalSlideIn 0.3s ease;
      }
      .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .modal-header h3 {
        margin: 0;
        color: #d97706;
      }
      .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #9ca3af;
      }
      .modal-body {
        padding: 20px;
      }
      .modal-footer {
        padding: 20px;
        text-align: center;
      }
      @keyframes modalSlideIn {
        from {
          opacity: 0;
          transform: translateY(-50px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
    `
    document.head.appendChild(style)

    // Añadir modal al DOM
    document.body.appendChild(modal)

    // Manejar cierre del modal
    const closeButtons = modal.querySelectorAll(".modal-close")
    closeButtons.forEach((button) => {
      button.addEventListener("click", () => {
        modal.style.opacity = "0"
        setTimeout(() => {
          if (modal.parentNode) {
            modal.parentNode.removeChild(modal)
            document.head.removeChild(style)
          }
        }, 300)
      })
    })

    // Cerrar al hacer clic fuera del modal
    modal.querySelector(".modal-overlay").addEventListener("click", (e) => {
      if (e.target === e.currentTarget) {
        closeButtons[0].click()
      }
    })
  }

  // Método para obtener estadísticas (para administradores)
  async getStats() {
    try {
      const response = await fetch(`${this.apiUrl}/stats`)
      const data = await response.json()
      return data.success ? data.data : null
    } catch (error) {
      console.error("Error obteniendo estadísticas:", error)
      return null
    }
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  new NewsletterManager()
})

// Exportar para uso en otros archivos
window.NewsletterManager = NewsletterManager
