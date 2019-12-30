<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\View;
use App\Services\Auth;
use Smarty;
use App\Services\Internationalization;

/**
 * BaseController
 */
class BaseController
{
    /**
     * @var Smarty
     */
    protected $view;

    /**
     * @var User
     */
    protected $user;

    /**
     * Construct page renderer
     */
    public function __construct()
    {
        $this->view = View::getSmarty();
        $this->user = Auth::getUser();
        $this->i18n = new Internationalization();
        $this->i18n->lang = $this->user->lang;
    }

    /**
     * Get smarty
     *
     * @return Smarty
     */
    public function view()
    {
        return $this->view;
    }
    /**
     * @param $response
     * @param $res
     * @return mixed
     */
    public function echoJson($response, $res)
    {
        return $response->getBody()->write(json_encode($res));
    }
}
