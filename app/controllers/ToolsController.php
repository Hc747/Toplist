<?php

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Mvc\View;
use Phalcon\Paginator\Adapter\NativeArray;

class ToolsController extends BaseController {

    public function indexAction() {

    }

    public function itemsAction($page = 1) {
        $data = $this->getList('items-complete');

        $itemList = (new NativeArray([
            'data'  => $data,
            'limit' => 50,
            'page'  => $page
        ]))->getPaginate();

        $this->view->itemList = $itemList;
    }

    public function searchAction() {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        $data   = $this->getList('items-complete');
        $search = $this->request->getPost("search", "string");
        $found  = [];

        if ($search != null && $search != '') {
            foreach ($data as $key => $value) {
                $itemName = $value['name'];
                if (stripos(strtolower($itemName), strtolower($search)) !== false) {
                    $found[] = $value;
                }
            }
        } else {
            $found = $data;
        }

        $itemList = (new NativeArray([
            'data'  => $found,
            'limit' => 50,
            'page'  => $this->request->getPost("page", "int", 1)
        ]))->getPaginate();

        $this->view->itemList = $itemList;
    }

    private function getList($name) {
        $cache    = new BackFile(new FrontData(['lifetime' => 1440 ]), [ 'cacheDir' =>  "../app/compiled/" ]);
        $itemList = $cache->get("$name.cache");

        if (!$itemList) {
            $itemList = json_decode(file_get_contents("../resources/$name.json"), true);
            $cache->save("$name.cache", $itemList);
        }

        return $itemList;
    }

}