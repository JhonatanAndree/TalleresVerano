<?php
/**
 * Controlador base
 * Ruta: includes/core/Controller.php
 */

abstract class Controller {
    protected $request;
    protected $response;
    protected $session;
    protected $security;
    protected $logger;

    public function __construct() {
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
        $this->session = Session::getInstance();
        $this->security = SecurityHelper::getInstance();
        $this->logger = ActivityLogger::getInstance();
    }

    protected function view($name, $data = []) {
        return $this->response->view($name, array_merge($data, [
            'session' => $this->session,
            'csrf_token' => $this->security->generateCsrfToken(),
            'flash' => $this->session->getFlash('message')
        ]));
    }

    protected function json($data) {
        return $this->response->json($data);
    }

    protected function redirect($url) {
        return $this->response->redirect($url);
    }

    protected function back() {
        return $this->response->back();
    }

    protected function validateRequest(array $rules) {
        $validator = new Validator($this->request->all());
        $validator->validate($rules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        return $validator->validated();
    }

    protected function authorize($action) {
        if (!$this->security->can($action)) {
            throw new UnauthorizedException();
        }
    }

    protected function user() {
        return $this->session->get('user');
    }

    protected function isAuthenticated() {
        return $this->session->has('user');
    }
}