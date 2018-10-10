<?php

namespace RestKit;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\HtmlResponse;
use CoreKit\Resolver;
use TemplateKit\Template;

abstract class AbstractResource
{
    protected $request;
    protected $templateResolver;
    protected $responseFormat;

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

        if ($this->responseFormat == 'html') {
            $html = $action;
            if ($this->templateResolver) {
                $file = $this->templateResolver->resolve('resource/'.$action);
                if ($file) {
                    $template = new Template($file);
                    foreach ($responseData as $key => $value) {
                        $template->assign($key, $value);
                    }
                    $html = $template->render();
                }
            }
            return new HtmlResponse($html);
        }

        return new JsonResponse($responseData);
    }

    public function trigger($event, ...$arguments)
    {
        if (method_exists($this, $event)) {
            call_user_func_array([ $this, $event ], $arguments);
        }
    }

    public function setTemplateResolver(Resolver $resolver)
    {
        $this->templateResolver = $resolver;
    }

    public function setResponseFormat(string $format)
    {
        $this->responseFormat = $format;
    }

    abstract public function index();
    abstract public function detail($id);
    abstract public function add();
    abstract public function update($id);
    abstract public function delete($id);
}
