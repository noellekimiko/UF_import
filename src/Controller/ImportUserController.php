<?php

namespace UserFrosting\Sprinkle\Import\Controller;

use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Admin\Controller\UserController;
use League\Csv\Reader;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;

class ImportUserController extends SimpleController
{
    /**
     * Processes the request to create a set of new users from a CSV (from the admin controls).
     *
     * Processes the request from the import users form
     * This route requires authentication.
     *
     * Request type: POST
     * @see getModalImport
     * @param  Request            $request
     * @param  Response           $response
     * @param  array              $args
     * @throws ForbiddenException If user is not authozied to access page
     */
    public function importCsv($request, $response, $args)
    {
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;
        
        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;
        
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_user')) {
            throw new ForbiddenException();
        }

        /** @var \UserFrosting\Support\Repository\Repository $config */
        $config = $this->ci->config;

        $csvFile = $args['csvFile'];
        $csv = Reader::createFromPath($_FILES['csvFile']['tmp_name'], 'r');

        $fm = $this->ci->factory;
        // Determine if there is a header row in the import file or not
        if ($config['import.header_row']) {
            $rows = $csv->fetchAssoc($config['import.header_index']);
        } else {
            $rows = $csv->fetchAssoc($config['import.header_keys']);
        }

        /** @var \UserFrosting\Sprinkle\Core\Alert\AlertStream $ms */
        $ms = $this->ci->alerts;
        
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $userCount = 0;
        foreach ($rows as $row) {
            try {
                // Check if username or email already exists
                if ($classMapper->staticMethod('user', 'findUnique', $row['user_name'], 'user_name')) {
                    $ms->addMessageTranslated('danger', 'USER.IMPORT.USERNAME_IN_USE', $row);
                    continue;
                }
                if ($classMapper->staticMethod('user', 'findUnique', $row['email'], 'email')) {
                    $ms->addMessageTranslated('danger', 'USER.IMPORT.EMAIL_IN_USE', $row);
                    continue;
                }

                $user = $fm->create(User::class, $row);
                $userCount += 1;
            } catch (Exception $e) {
                return $response->withJson([], 400);
            }
        }
        $ms->addMessageTranslated('success', 'USER.IMPORT.UPLOAD_SUCCESS', [
           'userCount' => $userCount
        ]);

        return $response->withJson([], 200);
    }

    /**
     * Renders the modal form for uploading a CSV
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     * @param  Request            $request
     * @param  Response           $response
     * @param  array              $args
     * @throws ForbiddenException If user is not authozied to access page
     */
    public function getModalImport($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();
        /** @var \UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager $authorizer */
        $authorizer = $this->ci->authorizer;
        /** @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface $currentUser */
        $currentUser = $this->ci->currentUser;
        /** @var \UserFrosting\I18n\MessageTranslator $translator */
        $translator = $this->ci->translator;
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'create_user')) {
            throw new ForbiddenException();
        }
        // Load validation rules
        $schema = new RequestSchema('schema://requests/user/import.yaml');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);
        return $this->ci->view->render($response, 'modals/import.html.twig', [
            'form'    => [
                'action'      => 'api/users/csv',
                'method'      => 'POST',
                'fields'      => $fields,
                'submit_text' => $translator->translate('USER.IMPORT.UPLOAD')
            ],
            'page' => [
                'validators' => $validator->rules('json', false)
            ]
        ]);
    }

}