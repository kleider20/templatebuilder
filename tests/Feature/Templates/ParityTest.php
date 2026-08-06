<?php

test('parity route sirve dist JS con content-type correcto', function () {
    $response = $this->get('/templates/aurora/dist/aurora.js');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/javascript');
});

test('parity route sirve dist CSS', function () {
    $this->get('/templates/aurora/dist/aurora.css')->assertOk();
});

test('parity route 404 para archivo inexistente', function () {
    $this->get('/templates/aurora/dist/no-existe.js')->assertNotFound();
});

test('parity route 404 para directorio no permitido', function () {
    $this->get('/templates/aurora/evil/file.js')->assertNotFound();
});

test('parity route bloquea path traversal en assets', function () {
    $this->get('/templates/aurora/assets/../template.json')->assertNotFound();
});
