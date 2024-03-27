# Store Fixtures

Fixture Defaults
```php
[
    'code' => 'klevu_test_store_1',
    'name' => 'Klevu Test Store 1',
    'website_id' => 1,
    'group_id' => 1,
    'is_active' => true,
    'key' => 'test_store',
]
```
Build with defaults
```php
$storeBuilder = StoreBuilder::addStore();
$storeBuilder->build();
```
Build with custom values
```php
$storeBuilder = StoreBuilder::addStore();
$storeBuilder->withCode('store_code');
$storeBuilder->withName('Store Name');
$storeBuilder->build();
```
Add store to fixtures pool and tag it for easy recall.
The tag/key does not need to match the store code
```php
$storeBuilder = StoreBuilder::addStore();

$this->storeFixturesPool->add(
    $storeBuilder->build(),
    'test_store'
);
```
Retrieve from fixtures pool using tag/key
```php
$store = $this->storeFixturesPool->get('test_store');
```

### Example

```php
use Klevu\TestFixtures\Store\StoreBuilder;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->storeFixturesPool = $this->objectManager->create(StoreFixturesPool::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->storeFixturesPool->rollback();
    }
    
    public function testSomething_withStoreDefaults(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        ...
    }
    
    public function testSomething_withStoreOverrides(): void 
    {
        $this->createStore([
            'code' => 'klevu_test_store_12345',
            'name' => 'Klevu Test Store',
            'is_active' => false,
            'key' => 'some_key',
        ]);
        $store = $this->storeFixturesPool->get('some_key');
        ...
    }
    
    /**
     * @param mixed[]|null $storeData
     *
     * @return void
     * @throws \Exception
     */
    private function createStore(?array $storeData = []): void
    {
        $storeBuilder = StoreBuilder::addStore();
        if (!empty($storeData['code'])) {
            $storeBuilder->withCode($storeData['code']);
        }
        if (!empty($storeData['name'])) {
            $storeBuilder->withName($storeData['name']);
        }
        if (isset($storeData['website_id'])) {
            $storeBuilder->withWebsiteId($storeData['website_id']);
        }
        if (isset($storeData['group_id'])) {
            $storeBuilder->withGroupId($storeData['group_id']);
        }
        if (isset($storeData['is_active'])) {
            $storeBuilder->withIsActive($storeData['is_active']);
        }

        $this->storeFixturesPool->add(
            $storeBuilder->build(),
            $storeData['key'] ?? 'test_store' // key used to retrieve store from storeFixturesPool::get
        );
    }
}
```
