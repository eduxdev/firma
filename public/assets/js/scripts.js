// scripts.js

// Configuración de las firmas
let signaturePad;

function initFirma() {
  const container = document.getElementById('firma-paciente');
  container.innerHTML = "";
  const canvas = document.createElement('canvas');
  canvas.width = container.clientWidth;
  canvas.height = container.clientHeight;
  container.appendChild(canvas);
  signaturePad = new SignaturePad(canvas);
}

function borrarFirma() {
  signaturePad.clear();
}

function guardarFirmas(e) {
  e.preventDefault();
  // Validar Sección 2: Quejas
  const quejasCheckboxes = document.querySelectorAll('input[name="quejas[]"]:checked');
  const otrosQuejas = document.getElementById('otros_quejas').value.trim();
  if (quejasCheckboxes.length === 0 && otrosQuejas === '') {
    document.getElementById('seccion-quejas').scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    return;
  }

  // Validar Sección 3: Afirmaciones
  const afirmacionesCheckboxes = document.querySelectorAll('input[name="afirmaciones[]"]:checked');
  const otrosAfirmaciones = document.getElementById('otros_afirmaciones').value.trim();
  if (afirmacionesCheckboxes.length === 0 && otrosAfirmaciones === '') {
    document.getElementById('seccion-afirmaciones').scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    return;
  }

  // Validar Sección 5: Declaraciones Legales
  const declaracionesCheckboxes = document.querySelectorAll('input[name="declaraciones[]"]');
  const allDeclaracionesChecked = Array.from(declaracionesCheckboxes).every(checkbox => checkbox.checked);

  if (!allDeclaracionesChecked) {
    document.getElementById('seccion-declaraciones').scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    return;
  }

  // Resto de validaciones y envío
  if (signaturePad.isEmpty()) {
    document.getElementById('firma-paciente').closest('.section').scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    Swal.fire('Error', 'La firma del paciente es obligatoria', 'error');
    return;
  }

  document.getElementById('firmaPaciente').value = signaturePad.toDataURL();

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

window.onload = initFirma;

// Configurar fecha máxima y cálculo de edad
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().split('T')[0];
  document.querySelector('input[name="fecha_nacimiento"]').setAttribute('max', today);

  // Event listener para cálculo de edad
  document.querySelector('input[name="fecha_nacimiento"]').addEventListener('change', calcularEdad);
});

function calcularEdad() {
  const fechaInput = document.querySelector('input[name="fecha_nacimiento"]');
  const edadInput = document.querySelector('input[name="edad"]');

  const fechaNacimiento = new Date(fechaInput.value);
  const hoy = new Date();

  // Validaciones
  if (fechaNacimiento > hoy) {
    alert("❌ Error: La fecha de nacimiento no puede ser futura");
    fechaInput.value = '';
    edadInput.value = '';
    return;
  }

  const fechaMinima = new Date();
  fechaMinima.setFullYear(hoy.getFullYear() - 150);
  if (fechaNacimiento < fechaMinima) {
    alert("❌ Error: La fecha excede el rango válido (máximo 150 años)");
    fechaInput.value = '';
    edadInput.value = '';
    return;
  }

  // Cálculo de edad
  let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
  const mes = hoy.getMonth() - fechaNacimiento.getMonth();

  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
    edad--;
  }

  edadInput.value = edad;
}

// Habilitar/deshabilitar campos condicionales
function toggleField(fieldId, isEnabled) {
  var field = document.getElementById(fieldId);
  field.disabled = !isEnabled;
  if (!isEnabled) {
    field.value = ''; // Limpiar campo si se deshabilita
  }
}

// Habilitar/deshabilitar campos de emergencia
document.addEventListener('DOMContentLoaded', function() {
  const menorEdad = document.getElementById('menor_edad');
  const camposEmergencia = [
    document.getElementById('contacto_emergencia'),
    document.getElementById('telefono_emergencia'),
    document.getElementById('relacion')
  ];

  function actualizarCampos() {
    const esMenor = menorEdad.value === 'Si';

    camposEmergencia.forEach(campo => {
      campo.disabled = !esMenor;
      campo.required = esMenor;
    });
  }

  // Ejecutar al cargar y cuando cambie la selección
  menorEdad.addEventListener('change', actualizarCampos);
  actualizarCampos(); // Estado inicial
});