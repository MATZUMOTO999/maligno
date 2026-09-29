document.addEventListener('DOMContentLoaded', () => {
    const btnBuscar = document.getElementById('btnBuscar');
    const inputDni = document.getElementById('identificador'); // Asegúrate que tu input tenga este ID
    const selectTipo = document.getElementById('tipoBusqueda'); // Asegúrate que tu select tenga este ID
    const divResultado = document.getElementById('resultadoBusqueda');

    btnBuscar.addEventListener('click', async () => {
        const dni = inputDni.value.trim();
        const tipo = selectTipo.value;

        // 1. Validaciones básicas en frontend
        if (!dni) {
            alert('Por favor ingresa un número de DNI.');
            return;
        }

        // Feedback visual de carga
        btnBuscar.textContent = 'Buscando...';
        btnBuscar.disabled = true;
        divResultado.style.display = 'none';

        try {
            // 2. Llamada a tu API (ver.php)
            const response = await fetch(`datos/ver.php?dni=${dni}&tipo=${tipo}`);
            const data = await response.json();

            // 3. Manejar la respuesta
            if (data.success) {
                mostrarDatos(data.data);
            } else {
                divResultado.innerHTML = `
                    <div class="alert alert-error">
                        ❌ ${data.message}
                    </div>`;
                divResultado.style.display = 'block';
            }

        } catch (error) {
            console.error('Error:', error);
            divResultado.innerHTML = `<div class="alert alert-error">Error de conexión con el servidor.</div>`;
            divResultado.style.display = 'block';
        } finally {
            // Restaurar botón
            btnBuscar.textContent = 'Buscar';
            btnBuscar.disabled = false;
        }
    });

    // Función para renderizar los datos bonitos
    function mostrarDatos(persona) {
        divResultado.innerHTML = `
            <div class="card-resultado">
                <h3>✅ Persona Encontrada</h3>
                <div class="grid-datos">
                    <p><strong>Nombre:</strong> ${persona.nombres || 'N/A'} ${persona.apellidos || ''}</p>
                    <p><strong>DNI:</strong> ${persona.dni || 'No disponible'}</p>
                    <p><strong>Rol:</strong> ${persona.rol_nombre || 'Sin rol asignado'}</p>
                    <p><strong>Carrera:</strong> ${persona.carrera_nombre || 'N/A'}</p>
                </div>
            </div>
        `;
        divResultado.style.display = 'block';
    }
});