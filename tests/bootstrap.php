<?php

// Minimal bootstrap for unit tests without full Craft app
require_once __DIR__ . '/../vendor/autoload.php';

// Provide a very small stubbed app with only services we touch in unit tests
class TestQueue {
    public array $pushed = [];
    public function push($job) { $this->pushed[] = $job; return count($this->pushed); }
}

class TestElementsService {
    public array $saved = [];
    public function saveElement($element) { $this->saved[] = $element; return true; }
    public function getElementById($id) { return null; }
}

class TestRequest {
    private array $body = [];
    private bool $isCp = true;
    private bool $isPost = true;
    public function setBody(array $b){$this->body = $b;}
    public function getRequiredBodyParam($name){ if(!array_key_exists($name,$this->body)) throw new \yii\web\BadRequestHttpException("Missing $name"); return $this->body[$name]; }
    public function getIsConsoleRequest(){ return false; }
}

class TestView {
    public function registerJsWithVars($cb, $vars){ /* noop */ }
    public function renderTemplate($t, $vars=[]){ return ''; }
}

class TestApp {
    public TestQueue $queue;
    public TestElementsService $elements;
    public TestRequest $request;
    public TestView $view;
    public function __construct(){
        $this->queue = new TestQueue();
        $this->elements = new TestElementsService();
        $this->request = new TestRequest();
        $this->view = new TestView();
    }
}

if (!class_exists('Craft')) {
    class Craft {
        public static $app;
        public static function t($cat, $msg, $params=[]){ return strtr($msg, array_combine(array_map(fn($k)=>"{".$k."}", array_keys($params)), array_values($params))); }
        public static function createGuzzleClient(array $config = []){ return new class {
            public function post($url, $opts){
                // Default fake response; tests can override via setGuzzleMock()
                return new class {
                    public function getBody(){ return json_encode(['choices'=>[['message'=>['content'=>'example alt']]]]); }
                };
            }
        }; }
        public static function error($msg, $method){ /* noop for tests */ }
    }
}

Craft::$app = new TestApp();

// Helper to let tests override Craft::createGuzzleClient
function setGuzzleMock(callable $factory){
    class_alias(get_class(new class{}), 'Tmp');
}
