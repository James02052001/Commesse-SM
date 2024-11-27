function validateFileSize() {
    const maxFileSize = 10 * 1024 * 1024; // 10 MB in bytes
    const files = document.getElementById('file').files;

    for (let i = 0; i < files.length; i++) {
        if (files[i].size > maxFileSize) {
            alert('Il file "' + files[i].name + '" supera il limite di 10 MB.');
            return false; // Blocca l'invio del modulo
        }
    }
    return true;
}