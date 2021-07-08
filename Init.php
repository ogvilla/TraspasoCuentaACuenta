<?php
namespace FacturaScripts\Plugins\TraspasoCuentaACuenta;

class Init extends \FacturaScripts\Core\Base\InitClass
{
    public function init() {
        $this->loadExtension(new Extension\Controller\ListAsiento());
    }

    public function update() {
        /// se ejecutar cada vez que se instala o actualiza el plugin
    }
}