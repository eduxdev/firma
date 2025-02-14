// Configuración de las firmas
let pads = [];
function initFirmas() {
  [1, 2].forEach(num => {
    const canvas = document.createElement('canvas');
    canvas.width = 300;
    canvas.height = 150;
    const containerId = num === 1 ? 'firma-paciente' : 'firma-medico';
    document.getElementById(containerId).appendChild(canvas);
    pads[num] = new SignaturePad(canvas);
  });
}

function borrarFirma(num) {
  pads[num].clear();
}

function guardarFirmas(e) {
  e.preventDefault();

  // Validar Sección 2: Quejas
  const quejasCheckboxes = document.querySelectorAll('input[name="quejas[]"]:checked');
  const otrosQuejas = document.getElementById('otros_quejas').value.trim();
  if (quejasCheckboxes.length === 0 && otrosQuejas === '') {
    Swal.fire({
      icon: 'error',
      title: 'Sección incompleta',
      text: '¡Por favor seleccione al menos una opción en "Quejas" o complete el campo Otros!',
    }).then(() => {
      document.getElementById('seccion-quejas').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      });
    });
    return;
  }

  // Validar Sección 3: Afirmaciones
  const afirmacionesCheckboxes = document.querySelectorAll('input[name="afirmaciones[]"]:checked');
  const otrosAfirmaciones = document.getElementById('otros_afirmaciones').value.trim();
  if (afirmacionesCheckboxes.length === 0 && otrosAfirmaciones === '') {
    Swal.fire({
      icon: 'error',
      title: 'Sección incompleta',
      text: '¡Por favor seleccione al menos una opción en "Afirmaciones" o complete el campo Otros!',
    }).then(() => {
      document.getElementById('seccion-afirmaciones').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      });
    });
    return;
  }

  // Validar firma del paciente
  if (pads[1].isEmpty()) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Por favor complete la firma del paciente',
    });
    return;
  }

  // Guardar firmas
  document.getElementById('firmaPaciente').value = pads[1].toDataURL();
  document.getElementById('firmaMedico').value = pads[2].isEmpty() ? '' : pads[2].toDataURL();

  // Enviar formulario
  Swal.fire({
    icon: 'success',
    title: 'Enviado',
    text: 'El formulario ha sido enviado correctamente.',
    showConfirmButton: false,
    timer: 2000,
  }).then(() => {
    e.target.submit();
  });
}

window.onload = initFirmas;

// Configurar fecha máxima al cargar la página
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().split('T')[0];
  document.querySelector('input[name="fecha_nacimiento"]').setAttribute('max', today);
});

function calcularEdad() {
  const fechaInput = document.querySelector('input[name="fecha_nacimiento"]');
  const edadInput = document.querySelector('input[name="edad"]');
  const menorEdadSelect = document.querySelector('select[name="menor_edad"]');
  
  const fechaNacimiento = new Date(fechaInput.value);
  const hoy = new Date();
  
  const resetCampos = () => {
    fechaInput.value = '';
    edadInput.value = '';
    menorEdadSelect.value = 'No';
  };

  if (fechaNacimiento > hoy) {
    alert("❌ Error: La fecha de nacimiento no puede ser futura");
    resetCampos();
    return;
  }

  const fechaMinima = new Date();
  fechaMinima.setFullYear(hoy.getFullYear() - 150);
  if (fechaNacimiento < fechaMinima) {
    alert("❌ Error: La fecha excede el rango válido (máximo 150 años)");
    resetCampos();
    return;
  }

  let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
  const mes = hoy.getMonth() - fechaNacimiento.getMonth();
  
  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
    edad--;
  }

  edadInput.value = edad;
  menorEdadSelect.value = edad < 18 ? 'Sí' : 'No';
}

document.querySelector('input[name="fecha_nacimiento"]').addEventListener('change', calcularEdad);

function toggleField(fieldId, isEnabled) {
  var field = document.getElementById(fieldId);
  field.disabled = !isEnabled;
  if (!isEnabled) {
    field.value = '';
  }
}

// Eliminar las validaciones antiguas que usaban alert()