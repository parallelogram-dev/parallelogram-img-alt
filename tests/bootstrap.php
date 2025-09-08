<?php

// Minimal bootstrap for unit tests without full Craft app
use yii\web\BadRequestHttpException;

require_once __DIR__ . '/../vendor/autoload.php';

// Provide a very small stubbed app with only services we touch in unit tests
class TestQueue {
    public array $pushed = [];
    public function push($job): int
    {$this->pushed[] = $job; return count($this->pushed); }
}

class TestElementsService {
    public array $saved = [];
    public function saveElement($element): true
    {$this->saved[] = $element; return true; }
    public function getElementById($id): null { return null; }
}

class TestRequest {
    private array $body = [];
    private bool $isCp = true;
    private bool $isPost = true;
    public function setBody(array $b): void {$this->body = $b;}

    /**
     * @throws BadRequestHttpException
     */
    public function getRequiredBodyParam($name){ if(!array_key_exists($name,$this->body)) throw new BadRequestHttpException("Missing $name"); return $this->body[$name]; }
    public function getIsConsoleRequest(): false { return false; }
}

class TestView {
    public function registerJsWithVars($cb, $vars){ /* noop */ }
    public function renderTemplate($t, $vars=[]): string { return ''; }
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
        public static function t($cat, $msg, $params=[]): string { return strtr($msg, array_combine(array_map(fn($k)=>"{".$k."}", array_keys($params)), array_values($params))); }
        public static function createGuzzleClient(array $config = []): object
        { return new class {
            public function post($url, $opts): object
            {
                // Default fake response; tests can override via setGuzzleMock()
                return new class {
                    public function getBody(): false|string { return json_encode(['choices' =>[['message' =>['content' =>'example alt']]]]); }
                };
            }
        }; }
        public static function error($msg, $method){ /* noop for tests */ }
    }
}

Craft::$app = new TestApp();

// Helper to let tests override Craft::createGuzzleClient
function setGuzzleMock(callable $factory): void
{
    class_alias(get_class(new class{}), 'Tmp');
}
