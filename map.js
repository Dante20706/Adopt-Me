document.addEventListener("DOMContentLoaded", () => {
  const mapContainer = document.getElementById("mapContainer");
  const locationsContainer = document.getElementById("locationsContainer");
  if (!mapContainer) return;

  let map;
  let userLocation = null;
  let markerGroups = {};
  let currentFilter = "all";
  const L = window.L;

  const defaultCoords = { lat: -24.7821, lng: -65.4232 }; // Salta Capital

  // ============================
  // 🔹 Inicialización del mapa
  // ============================
  function initializeMap(lat = defaultCoords.lat, lng = defaultCoords.lng, zoom = 13) {
    map = L.map("mapContainer").setView([lat, lng], zoom);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(map);

    setupMapFeatures();
    addMarkersToMap(ubicacionesBD); // Agregamos todos los marcadores al iniciar
  }

  function setupMapFeatures() {
    window.mapIcons = {
      vet: L.icon({ iconUrl: "img/icon-vet.png", iconSize: [32, 32] }),
      shelter: L.icon({ iconUrl: "img/icon-refugio.png", iconSize: [32, 32] }),
      petshop: L.icon({ iconUrl: "img/icon-petshop.png", iconSize: [32, 32] }),
      park: L.icon({ iconUrl: "img/icon-park.png", iconSize: [32, 32] }),
      default: L.icon({ iconUrl: "img/icon-default.png", iconSize: [32, 32] }),
    };

    markerGroups = {
      all: L.layerGroup().addTo(map),
      vet: L.layerGroup(),
      shelter: L.layerGroup(),
      petshop: L.layerGroup(),
      park: L.layerGroup(),
    };

    setupFilters();
  }

  // ============================
  // 🔹 Ubicación del usuario
  // ============================
  function getUserLocation() {
    if (!navigator.geolocation) {
      console.warn("Geolocalización no soportada, usando Salta por defecto");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      pos => {
        userLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        map.setView([userLocation.lat, userLocation.lng], 15);
        addUserMarker(userLocation.lat, userLocation.lng);
      },
      err => {
        console.warn("No se pudo obtener la ubicación, usando Salta por defecto", err);
      },
      { enableHighAccuracy: true, timeout: 5000 }
    );
  }

  function addUserMarker(lat, lng) {
    L.marker([lat, lng], {
      icon: L.icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png",
        shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
      }),
    }).addTo(map).bindPopup("Tu ubicación actual").openPopup();
  }

  // ============================
  // 🔹 Marcadores y tarjetas
  // ============================
  function addMarkersToMap(data) {
    Object.values(markerGroups).forEach(g => g.clearLayers());

    data.forEach(u => {
      const lat = parseFloat(u.latitud);
      const lng = parseFloat(u.longitud);
      if (isNaN(lat) || isNaN(lng)) return;

      const icon = window.mapIcons[u.tipo] || window.mapIcons.default;
      const marker = L.marker([lat, lng], { icon }).bindPopup(`
        <div>
          <strong>${u.nombre}</strong><br>
          <small>${u.direccion || ""}</small><br>
          ${u.telefono ? `<i class="fas fa-phone"></i> ${u.telefono}<br>` : ""}
          ${u.horario ? `<i class="fas fa-clock"></i> ${u.horario}<br>` : ""}
          ${u.sitio_web ? `<a href="${u.sitio_web}" target="_blank"><i class="fas fa-globe"></i> Sitio web</a>` : ""}
        </div>
      `);

      marker.addTo(markerGroups.all);
      if (markerGroups[u.tipo]) marker.addTo(markerGroups[u.tipo]);
    });

    // Por defecto mostrar todos
    map.addLayer(markerGroups.all);
    renderLocationCards(data);
  }

  function setupFilters() {
    document.querySelectorAll(".filter-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        currentFilter = btn.dataset.type;

        // Limpiar todas las capas
        Object.values(markerGroups).forEach(g => map.removeLayer(g));

        if (currentFilter === "all") {
          map.addLayer(markerGroups.all);
        } else {
          map.addLayer(markerGroups[currentFilter]);
        }

        const filtered = currentFilter === "all"
          ? ubicacionesBD
          : ubicacionesBD.filter(u => u.tipo === currentFilter);

        renderLocationCards(filtered);
      });
    });
  }

  function renderLocationCards(data) {
    locationsContainer.innerHTML = "";
    if (!data.length) {
      locationsContainer.innerHTML = "<p>No se encontraron lugares.</p>";
      return;
    }

    data.forEach(u => {
      const icon = {
        vet: "fa-stethoscope",
        shelter: "fa-home",
        petshop: "fa-shopping-bag",
        park: "fa-tree",
      }[u.tipo] || "fa-map-marker-alt";

      const card = document.createElement("div");
      card.className = "location-card";
      card.innerHTML = `
        <div class="location-type ${u.tipo}">
          <i class="fas ${icon}"></i>
        </div>
        <h3>${u.nombre}</h3>
        <p><i class="fas fa-map-marker-alt"></i> ${u.direccion || ""}</p>
        ${u.telefono ? `<p><i class="fas fa-phone"></i> ${u.telefono}</p>` : ""}
        ${u.horario ? `<p><i class="fas fa-clock"></i> ${u.horario}</p>` : ""}
      `;
      card.addEventListener("click", () => map.setView([parseFloat(u.latitud), parseFloat(u.longitud)], 16));
      locationsContainer.appendChild(card);
    });
  }

  // ============================
  // 🔹 Ejecutar
  // ============================
  initializeMap();
  getUserLocation();
});
