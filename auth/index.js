const imagenes = ["imagen1.jpg", "imagen2.jpg", "imagen3.jpg"];
    let indiceImagenActual = 0;

    function cambiarImagen(direccion) {
        indiceImagenActual = (indiceImagenActual + direccion + imagenes.length) % imagenes.length;
        const imagenPrincipal = document.getElementById("imagenPrincipal");
        imagenPrincipal.src = imagenes[indiceImagenActual];

        actualizarPuntos();
    }

    function cambiarImagenDesdeMiniatura(indice) {
        indiceImagenActual = indice;
        const imagenPrincipal = document.getElementById("imagenPrincipal");
        imagenPrincipal.src = imagenes[indice];

        actualizarPuntos();
    }

    function actualizarPuntos() {
        const puntosContainer = document.getElementById("puntos");
        puntosContainer.innerHTML = "";

        imagenes.forEach((imagen, index) => {
            const punto = document.createElement("span");
            punto.className = (index === indiceImagenActual) ? "punto puntoActivo" : "punto";
            punto.onclick = () => cambiarImagenDesdeMiniatura(index);
            puntosContainer.appendChild(punto);
        });
    }

actualizarPuntos();