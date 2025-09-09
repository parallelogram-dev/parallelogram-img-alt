<?php

// Minimal bootstrap for unit tests without full Craft app

// Predefine a stub for craft\elements\Asset before Composer loads real classes
if (!class_exists('craft\\elements\\Asset', false)) {
    class AssetPreStub { public ?int $id = null; public string $filename = ''; public string $mimeType = 'image/jpeg'; public ?string $alt = null; public static array $registry = []; public static function find() { return new AssetQueryPreStub(); } }
    class AssetQueryPreStub { private ?array $ids = null; private $volume = null; private ?bool $hasAlt = null; public function volume($v){ $this->volume = $v; return $this; } public function hasAlt($b){ $this->hasAlt = $b; return $this; } public function id(int $id){ $this->ids = [$id]; return $this; } public function one(){ $all = $this->all(); return $all[0] ?? null; } public function all(): array { if ($this->ids !== null) { $out = []; foreach ($this->ids as $id) { if (isset(AssetPreStub::$registry[$id])) { $out[] = AssetPreStub::$registry[$id]; } } return $out; } return array_values(AssetPreStub::$registry); } }
    class_alias(AssetPreStub::class, 'craft\\elements\\Asset');
    // Convenience alias for tests to access the registry easily
    class_alias(AssetPreStub::class, 'AssetStub');
}
// Ensure global AssetStub alias always exists, even if craft\\elements\\Asset was defined elsewhere first
if (!class_exists('AssetStub', false) && class_exists('craft\\elements\\Asset', false)) {
    class_alias('craft\\elements\\Asset', 'AssetStub');
}

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
     * @throws \RuntimeException
     */
    public function getRequiredBodyParam($name){ if(!array_key_exists($name,$this->body)) throw new \RuntimeException("Missing $name"); return $this->body[$name]; }
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
    public function getElements(): TestElementsService { return $this->elements; }
    public function getQueue(): TestQueue { return $this->queue; }
    public function getRequest(): TestRequest { return $this->request; }
    public function getView(): TestView { return $this->view; }
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

if (!class_exists('Yii')) {
    class Yii {
        public static $app;
        public static function t($category, $message, $params = [], $language = null): string {
            if (is_array($params) && $params) {
                $message = strtr($message, array_combine(array_map(fn($k)=>"{".$k."}", array_keys($params)), array_values($params)));
            }
            return $message;
        }
        public static function createObject($type)
        {
            $class = $type;
            $config = [];
            if (is_array($type)) {
                $config = $type;
                $class = $config['class'] ?? null;
                unset($config['class']);
            }
            if (!is_string($class) || $class === '') {
                throw new \InvalidArgumentException('Invalid class for Yii::createObject');
            }
            // Pass config to constructor to mimic Yii BaseObject behavior
            return new $class($config);
        }
        public static function configure($object, array $properties)
        {
            foreach ($properties as $name => $value) {
                $object->$name = $value;
            }
            return $object;
        }
    }
}

Craft::$app = new TestApp();

// Helper to let tests override Craft::createGuzzleClient
function setGuzzleMock(callable $factory): void
{
    class_alias(get_class(new class{}), 'Tmp');
}


// ---- Additional stubs for controller/action/job/resolver tests ----
if (!class_exists('yii\\web\\Response', false)) { class ResponseStub {} class_alias(ResponseStub::class, 'yii\\web\\Response'); }
if (!class_exists('yii\\web\\BadRequestHttpException', false)) { class BadRequestHttpExceptionStub extends \RuntimeException {} class_alias(BadRequestHttpExceptionStub::class, 'yii\\web\\BadRequestHttpException'); }
if (!class_exists('yii\\console\\ExitCode', false)) { class ExitCodeStub { const OK = 0; const UNSPECIFIED_ERROR = 1; } class_alias(ExitCodeStub::class, 'yii\\console\\ExitCode'); }

if (!class_exists('craft\\web\\Controller', false)) { class WebControllerStub { protected array|int|bool $allowAnonymous = false; protected function requireCpRequest(): void {} protected function requirePostRequest(): void {} protected function requireAcceptsJson(): void {} protected function asSuccess(string $message) { return new \yii\web\Response(); } } class_alias(WebControllerStub::class, 'craft\\web\\Controller'); }
if (!class_exists('craft\\console\\Controller', false)) { class ConsoleControllerStub { protected function stdout($s){/*noop*/} public function note($m){$this->stdout($m);} public function warning($m){$this->stdout($m);} public function success($m){$this->stdout($m);} } class_alias(ConsoleControllerStub::class, 'craft\\console\\Controller'); }

if (!class_exists('craft\\base\\ElementAction', false)) { class ElementActionStub { private string $message = ''; public function setMessage(string $m): void { $this->message = $m; } public function getMessage(): string { return $this->message; } } class_alias(ElementActionStub::class, 'craft\\base\\ElementAction'); }
if (!class_exists('craft\\queue\\BaseJob', false)) { abstract class BaseJobStub { public function __construct(array $config = []) { foreach ($config as $k=>$v) { $this->$k = $v; } } } class_alias(BaseJobStub::class, 'craft\\queue\\BaseJob'); }

if (!class_exists('craft\\db\\Query', false)) { class QueryStub { public function select($c){return $this;} public function from($t){return $this;} public function where($w){return $this;} public function column(): array { return []; } } class_alias(QueryStub::class, 'craft\\db\\Query'); }
