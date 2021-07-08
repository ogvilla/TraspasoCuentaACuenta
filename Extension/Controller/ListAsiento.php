<?php
namespace FacturaScripts\Plugins\TraspasoCuentaACuenta\Extension\Controller;
/**
 * This file is part of TraspasoCuentaACuenta plugin for FacturaScripts
 * Copyright (C) 2021 Oscar G. Villa González <ogvilla@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use FacturaScripts\Dinamic\Model\Partida as DinPartida;

/**
 * Description of ListAsiento (Extension)
 *
 * @author Oscar G. Villa González <ogvilla@gmail.com>
 */
class ListAsiento
{
    protected function createViews()
    {
        return function() {
            $this->addButton('ListAsiento', [
                'action' => 'transfer-movements',
                'icon' => 'fas fa-exchange-alt',
                'label' => 'transfer-movements-account-to-account',
                'type' => 'modal'
            ]);
        };
    }

    protected function execPreviousAction()
    {
        return function($action) {
            if($action !== 'transfer-movements') {
                return true;
            }

            $params = $this->request->request->all();
            if (empty($params)) {
                return true;
            }

            $sql = 'SELECT idpartida '
            . ' FROM partidas'
            . ' INNER JOIN asientos ON asientos.idasiento = partidas.idasiento'
            . ' WHERE ' . $this->getDataWhere($params) . ';';

            // este select recoge las partidas que tienen la codsubcuenta y los
            // parametros que buscamos.
            // hay que seleccionar las partidas con los mismos números de asiento que la selección anterior 
            // que tienen  codcontrapartida = originaaccount y modificarlos también. Revisar si con este select 
            // se realiza esto correctamente o se debe realizar otro select y otra transferencia de
            // movimientos a parte
            $data = $this->dataBase->select($sql);
            $this->transferMovements($data, $params['destinyaccount'], $params['originaccount']);

            // añadir un mensaje de éxito
            // revisar como afecta al libro de facturas de iva por si hay que hacer algo                        
        };
    }
   
    /**
     *
     * @param array $params
     *
     * @return string
     */
    protected function getDataWhere()
    {
            return function(array $params = []) {
            $where = 'partidas.codsubcuenta = ' . $this->dataBase->var2str($params['originaccount'])
            . ' OR partidas.codcontrapartida = ' . $this->dataBase->var2str($params['originaccount'])
            . ' AND asientos.fecha BETWEEN ' . $this->dataBase->var2str($params['startdate'])
            . ' AND  ' . $this->dataBase->var2str($params['enddate']);

            if (!empty($params['iddiario'])) {
                $where .= ' AND asientos.iddiario = ' . $this->dataBase->var2str($params['iddiario']);                   
            }            
            
            return $where;
        };        
    }

    protected function transferMovements(){
        return function ($idpartidas, $destinyaccount) {
            $modelPartida = new DinPartida();
            foreach ($idpartidas as $idpartida) {
                $project = $modelPartida->get($idpartida['idpartida']);
                if ($project) {
                    if ($project->codcontrapartida && $project->codsubcuenta != $destinyaccount) {
                        $project->codcontrapartida = $destinyaccount;
                    } else {
                        $project->codsubcuenta = $destinyaccount;
                    }                    
                    $project->save();
                }                           
            } 
        }; 
    }

    /* protected function loadData()
    {
        return function ($viewName, $view) {
            switch ($viewName) {
                case 'ListAsiento':
                    $codeExercise = $this->getViewModelValue($viewName, 'codejercicio');
                    $exercise = new \FacturaScripts\Dinamic\Model\Ejercicio();
                    $exercise->loadFromCode($codeExercise);
                    $fromDate = $this->views[$viewName]->columnModalForName('from-date');                                      
                    if ($fromDate) {
                        $fromDate->widget->setCustomValue($exercise->fechainicio);                                            
                    }
                    $untilDate = $this->views[$viewName]->columnModalForName('until-date');
                    if ($untilDate) {
                        $untilDate->widget->setCustomValue($exercise->fechafin);
                    }
                    // esto no funciona porque el widget está en un modal
            }
            
        };
        
    } */
}
