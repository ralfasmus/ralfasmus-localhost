
function isString(v) {
    return (typeof v === 'string' || v instanceof String);
}

function newId() {
    return 'id' + jetzt('YYYY-MM-DD-HH-mm-ss');
}