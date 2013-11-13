<?php

/* 
__PocketMine Plugin__ 
name=SignPortal
description=Multiworld portal plugin
version=1.0.1
author=99leonchang
class=SignPortal
apiversion=10
*/

class SignPortal implements Plugin{
    private $api, $server, $path;
    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
        $this->server = ServerAPI::request();
    }

    public function init(){
        $this->api->addHandler("player.block.touch", array($this, "eventHandler"));
        $this->api->addHandler("tile.update", array($this, "eventHandler"));
        $this->api->console->register("signportal", "[SignPortal] Adds an admin that can create portals", array($this, 'commandH'));
        $this->api->console->alias("sp", "signportal");
        $this->config = new Config($this->api->plugin->configPath($this)."config.yml", CONFIG_YAML, array(
                "USEPERMISSIONS" => 1,
                "admins" => array()
            ));

    }
    public function __destruct() {}

    public function commandH($cmd, $params) {
        switch($cmd)
            $this->config->reload();
            switch (array_shift($params)) {
                case 'add':
                    $usrname = (int) array_shift($params);
                    if(!in_array($usrname, $this->config->get('admins'))) {
                        $co = $this->config->get('admins');
                        array_push($c, $usrname);
                        $this->config->set('admins', $co);
                        $this->config->save();
                        $this->config->reload();
                    }
                    break;

                case 'remove':
                    $usrname = (int) array_shift($params);
                    if(in_array($usrname, $this->config->get('admins'))) {
                        $co = $this->config->get("admins");
                        $key = array_search($usrname, $co);
                        unset($c[$key]);
                        $this->config->set("admins", $c);
                        $this->config->save();
                        $this->config->reload();
                    }
                    break;
            }
        }
    }

    public function eventHandler(&$data, $event){
        switch ($event) {
            case "tile.update":
                if ($data->class === TILE_SIGN) {
                    $usrname = $data->data['creator'];
                    $user_permission = $this->api->dhandle("get.player.permission", $usrname);
                    if ($this->config->get("USEPERMISSIONS") == 1){
                        if ($data->data['Text1'] == "[WORLD]"){
                            if ($user_permission !== "ADMIN") {
                                $data->data['Text1'] = "[BROKEN]";
                                $this->api->chat->sendTo(false, "[SignPortal] Only admins can create portals!", $usrname);
                                return false;
                            }
                            else{
                                $mapname = $data->data['Text2'];
                                if ($this->api->level->loadLevel($mapname) === false) {
                                    $data->data['Text1'] = "[BROKEN]";
                                    $this->api->chat->sendTo(false, "[SignPortal] World $mapname not found!", $usrname);
                                    return false;
                                }
                                return true;
                            }
                        }
                    }
                    else {
                        if ($data->data['Text1'] == "[WORLD]"){
                            $this->config->reload();
                            if(in_array($usrname, $this->config->get('admins'))){
                                $mapname = $data->data['Text2'];
                                if ($this->api->level->loadLevel($mapname) === false) {
                                    $data->data['Text1'] = "[BROKEN]";
                                    $this->api->chat->sendTo(false, "[SignPortal] World $mapname not found!", $usrname);
                                    return false;
                                }
                                return true;
                            }
                            else {
                                $mapname = $data->data['Text2'];
                                if ($this->api->level->loadLevel($mapname) === false) {
                                    $data->data['Text1'] = "[BROKEN]";
                                    $this->api->chat->sendTo(false, "[SignPortal] World $mapname not found!", $usrname);
                                    return false;
                                }
                            }
                        }
                    }
                }
                break;
            case "player.block.touch":
                $tile = $this->api->tile->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $data['target']->level));
                if ($tile === false) break;
                $class = $tile->class;
                switch ($class) {
                    case TILE_SIGN:
                        switch ($data['type']) {
                            case "place":
                                if ($tile->data['Text1'] == "[WORLD]") {
                                    $mapname = $tile->data['Text2'];
                                    if ($this->api->level->loadLevel($mapname) === false) {
                                        $data->sendChat("[SignPortal] World $mapname not found");
                                    }
                                    else {
                                        $this->api->level->loadLevel($mapname);
                                        $data["player"]->teleport($this->api->level->get($mapname)->getSpawn());
                                    }
                                }
                                break;
                        }
                        break;
                }
                break;
        }

    }
}
