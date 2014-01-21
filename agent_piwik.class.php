<?php

class agent_piwik extends lw_agent
{

    protected $config;
    protected $request;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->config = $this->conf;
        $this->className = "agent_piwik";
        $this->adminSurfacePath = $this->config['path']['agents'] . "adminSurface/templates/";

        $usage = new lw_usage($this->className, "0");
        $this->secondaryUser = $usage->executeUsage();

        include_once(dirname(__FILE__) . '/Services/Autoloader.php');
        $autoloader = new \AgentPiwik\Services\Autoloader();
    }

    protected function showEdit()
    {
        $response = new \AgentPiwik\Services\Response();
        $controller = new \AgentPiwik\Controller\PiwikController($this->config, $response, $this->request);
        $controller->execute();
        return $response->getOutputByKey("AgentPiwik");
    }

    protected function buildNav()
    {
        $view = new \AgentPiwik\Views\Navigation();
        return $view->render();
    }

    protected function deleteAllowed()
    {
        return true;
    }

}