<?php

namespace refaltor\CustomItems\Loader;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\Server;
use pocketmine\utils\Config;
use refaltor\CustomItems\Items\Armor;
use refaltor\CustomItems\Items\Items;
use refaltor\CustomItems\Main;
use ReflectionObject;
use const pocketmine\RESOURCE_PATH;

class Load
{
    public static $data = [];
    public static $entries = [];
    public static $simpleNetToCoreMapping = [];
    public static $simpleCoreToNetMapping = [];

    public static function init(){

        $array = ["r16_to_current_item_map" => ["simple" => []], "item_id_map" => [], "required_item_list" => []];
        $config = Main::getInstance()->getConfig();

        foreach ($config->get("add") as $name => $keys){
            $array['r16_to_current_item_map']['simple']["minecraft:$name"] = "minecraft:custom_$name";
            $array['item_id_map']["minecraft:$name"] = $keys['id'];
            $array['required_item_list']["minecraft:$name"] = ["runtime_id" => $keys['id'], "component_based" => false];
        }


        $data = file_get_contents(RESOURCE_PATH . '/vanilla/r16_to_current_item_map.json');
        $json = json_decode($data, true);
        $add = $array["r16_to_current_item_map"];
        $json["simple"] = array_merge($json["simple"], $add["simple"]);

        $legacyStringToIntMapRaw = file_get_contents(RESOURCE_PATH . '/vanilla/item_id_map.json');
        $add = $array["item_id_map"];
        $legacyStringToIntMap = json_decode($legacyStringToIntMapRaw, true);
        $legacyStringToIntMap = array_merge($add, $legacyStringToIntMap);

        /** @phpstan-var array<string, int> $simpleMappings */
        $simpleMappings = [];
        foreach($json["simple"] as $oldId => $newId){
            $simpleMappings[$newId] = $legacyStringToIntMap[$oldId];
        }
        foreach($legacyStringToIntMap as $stringId => $intId){
            $simpleMappings[$stringId] = $intId;
        }

        /** @phpstan-var array<string, array{int, int}> $complexMappings */
        $complexMappings = [];
        foreach($json["complex"] as $oldId => $map){
            foreach($map as $meta => $newId){
                $complexMappings[$newId] = [$legacyStringToIntMap[$oldId], (int) $meta];
            }
        }


        $old = json_decode(file_get_contents(RESOURCE_PATH  . '/vanilla/required_item_list.json'), true);
        $add = $array["required_item_list"];
        $table = array_merge($old, $add);
        $params = [];
        foreach($table as $name => $entry){
            $params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"]);
        }
        self::$entries = $entries = (new ItemTypeDictionary($params))->getEntries();
        foreach($entries as $entry){
            $stringId = $entry->getStringId();
            $netId = $entry->getNumericId();
            if (isset($complexMappings[$stringId])){
            }elseif(isset($simpleMappings[$stringId])){
                self::$simpleCoreToNetMapping[$simpleMappings[$stringId]] = $netId;
                self::$simpleNetToCoreMapping[$netId] = $simpleMappings[$stringId];
            }
        }
    }

    public static function register(){
        $data = Main::getInstance()->getConfig();
        foreach ($data->getAll()["add"] as $name => $keys){
            if (!ItemFactory::isRegistered($keys['id'])){
                if ($keys['type'] === 'armor') ItemFactory::registerItem(new Armor($keys['defense'], $keys['durability'], $keys['id'], 0, $keys['name'], $keys['type']));
                if ($keys['type'] === 'item') ItemFactory::registerItem(new Item($keys['id'], 0, $keys['name']));
                Item::addCreativeItem(Item::get($keys['id']));
                Server::getInstance()->getLogger()->info(Item::get($keys['id'])->getName() . 'Register');
            }
        }
        self::init();
        $instance = ItemTranslator::getInstance();
        $ref = new ReflectionObject($instance);
        $r1 = $ref->getProperty("simpleCoreToNetMapping");
        $r2 = $ref->getProperty("simpleNetToCoreMapping");
        $r1->setAccessible(true);
        $r2->setAccessible(true);
        $r1->setValue($instance, self::$simpleCoreToNetMapping);
        $r2->setValue($instance, self::$simpleNetToCoreMapping);
    }
}