document.addEventListener("DOMContentLoaded", () => {
  const mapContainer = document.getElementById("mapContainer");
  const locationsContainer = document.getElementById("locationsContainer");

  if (!mapContainer) return;

  let map, userLocation = null;
  let markerGroups = {};
  let currentFilter = "all";
  const L = window.L;

  // ============================
  // 🔹 Inicialización del mapa
  // ============================
  function initializeMap(lat = -24.7821, lng = -65.4232, zoom = 13) {
    map = L.map("mapContainer").setView([lat, lng], zoom);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(map);

    setupMapFeatures();
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
  // 🔹 Obtener ubicación del usuario
  // ============================
  function getUserLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        pos => {
          userLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
          initializeMap(userLocation.lat, userLocation.lng, 15);

          L.marker([userLocation.lat, userLocation.lng], {
            icon: L.icon({
              iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png",
              shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
              iconSize: [25, 41],
              iconAnchor: [12, 41],
            }),
          }).addTo(map).bindPopup("Tu ubicación actual").openPopup();

          showNearbyLocations();
        },
        () => {
          initializeMap();
          addMarkersToMap(ubicacionesBD);
        }
      );
    } else {
      initializeMap();
      addMarkersToMap(ubicacionesBD);
    }
  }

  // ============================
  // 🔹 Mostrar ubicaciones cercanas
  // ============================
  function showNearbyLocations() {
    const radio = 5; // km
    const cercanas = ubicacionesBD.filter(u => {
      const dist = calcularDistancia(userLocation.lat, userLocation.lng, u.latitud, u.longitud);
      return dist <= radio;
    });
    addMarkersToMap(cercanas);
  }

  function calcularDistancia(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
              Math.cos(lat1 * Math.PI / 180) *
              Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  // ============================
  // 🔹 Marcadores y tarjetas
  // ============================
  function addMarkersToMap(data) {
    Object.values(markerGroups).forEach(g => g.clearLayers());

    data.forEach(u => {
      const icon = window.mapIcons[u.tipo] || window.mapIcons.default;
      const marker = L.marker([u.latitud, u.longitud], { icon })
        .bindPopup(`
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

    renderLocationCards(data);
  }

  function setupFilters() {
    document.querySelectorAll(".filter-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        currentFilter = btn.dataset.type;

        map.eachLayer(layer => {
          if (layer instanceof L.LayerGroup) map.removeLayer(layer);
        });

        if (currentFilter === "all") map.addLayer(markerGroups.all);
        else map.addLayer(markerGroups[currentFilter]);

        const filtered = currentFilter === "all"
          ? ubicacionesBD
          : ubicacionesBD.filter(u => u.tipo === currentFilter);
        renderLocationCards(filtered);
      });
    });
  }

  function renderLocationCards(data) {
    locationsContainer.innerHTML = "";
    if (data.length === 0) {
      locationsContainer.innerHTML = "<p>No se encontraron lugares cercanos.</p>";
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
      card.addEventListener("click", () => map.setView([u.latitud, u.longitud], 16));
      locationsContainer.appendChild(card);
    });
  }

  getUserLocation();
});
