<?php

namespace RestKit;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

abstract class AbstractResource
{
    protected $request;

    public function __invoke(ServerRequestInterface $request)
    {
        $this->request = $request;

        $action = 'index';
        $method = $request->getMethod();
        $id = $request->getAttribute('id');

        if ($method == 'POST' || $method == 'PUT') {
            $action = $id ? 'update' : 'add';
        } else if ($method == 'DELETE') {
            if (!$id) {
                throw new BadRequestException('DELETE request should provide an ID');
            }
            $action = 'delete';
        } else if ($method == 'GET') {
            $action = $id ? 'detail' : 'index';
        } else {
            throw new BadRequestException('Method ' . $method . ' not supported');
        }

        $user = AuthKit\Identity::getCurrent();
        $role = $user ? $user->role : 'Guest';
        $isAllowed = AuthKit\Acl::isAllowed($this->name, $role, $action);
        if (!$isAllowed) {
            throw new NotAllowedException('The action \'' . $action . '\' is not allowed for this user');
        }

        if ($id) {
            $result = $this->$action($id);
        }
        $result = $this->action();

        $responseData = [
            'success' => true,
            'data' => $result
        ];
        return new JsonResponse($responseData);
    }

    public function trigger($event, ...$arguments)
    {
        if (method_exists($this, $event)) {
            call_user_func_array([ $this, $event ], $arguments);
        }
    }

    abstract public function index();
    abstract public function detail($id);
    abstract public function add();
    abstract public function update($id);
    abstract public function delete($id);
}
