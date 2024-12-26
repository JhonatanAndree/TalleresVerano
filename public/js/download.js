const BASE_URL = 'https://muniporvenir.gob.pe/talleresdeverano';

function descargarFicha(dni) {
    realizarDescarga('ficha', dni);
}

function descargarCardID(dni) {
    realizarDescarga('card', dni);
}

function realizarDescarga(tipo, dni) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${BASE_URL}/controllers/download_controller.php`;
    
    const inputTipo = document.createElement('input');
    inputTipo.type = 'hidden';
    inputTipo.name = 'tipo';
    inputTipo.value = tipo;
    
    const inputDni = document.createElement('input');
    inputDni.type = 'hidden';
    inputDni.name = 'dni';
    inputDni.value = dni;
    
    form.appendChild(inputTipo);
    form.appendChild(inputDni);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}