<?php
use UserFrosting\Sprinkle\Core\Util\NoCache;

$app->group('/api/users', function () {
    $this->post('/csv', 'UserFrosting\Sprinkle\Import\Controller\ImportUserController:importCsv');
})->add(new NoCache());

$app->group('/modals/users', function () {
    $this->get('/import', 'UserFrosting\Sprinkle\Import\Controller\ImportUserController:getModalImport');
})->add('authGuard')->add(new NoCache());