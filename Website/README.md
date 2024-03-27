# Website Fixtures

Add Test fixtures based on https://github.com/tddwizard/magento2-fixtures

Fixture Defaults
```php
[
    'code' => 'klevu_test_website_1',
    'name' => 'Klevu Test Website 1',
    'default_group_id' => 1,
    'key' => 'test_website',
]
```
Build with defaults
```php
$websiteBuilder = WebsiteBuilder::addWebsite();
$websiteBuilder->build();
```
Build with custom values
```php
$websiteBuilder = WebsiteBuilder::addWebsite();
$websiteBuilder->withCode('test-website-10');
$websiteBuilder->withName('Test Website 10');
$websiteBuilder->withDefaultGroupId(10);
$websiteBuilder->build();
```
Add user to fixtures pool and tag it for easy recall.
The tag/key does not need to match the store code
```php
$websiteBuilder = WebsiteBuilder::addUser();

$this->websiteFixturesPool->add(
    $websiteBuilder->build(),
    'website_10'
);
```
Retrieve from fixtures pool using tag/key
```php
$store = $this->websiteFixturesPool->get('website_10');
```

Data Fixture to create new User
```php
use WebsiteTrait;

$this->createWebsite([
    'code' => 'some_code',
    'name' => 'A Name',
    'default_group_id' => 22,
    'key' => 'some_key',
]);
```

### Example with UserTrait

```php
use Klevu\TestFixtures\Website\WebsiteTrait;
use Klevu\TestFixtures\Website\WebsiteFixturesPool;
use Klevu\TestFixtures\Website\WebsiteTrait;
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    use WebsiteTrait;
     
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->websiteFixturesPool = $this->objectManager->create(WebsiteFixturesPool::class);
    }

    protected function tearDown(): void
    {
        $this->websiteFixturesPool->rollback();
    }
    
    public function testSomething_withWebsiteDefaults(): void
    {
        $this->createUser();
        $store = $this->websiteFixturesPool->get('test_website');
        ...
    }
    
    public function testSomething_withWebsiteOverrides(): void 
    {
        $this->createUser([
            'code' => 'some_code',
            'name' => 'A Name',
            'default_group_id' => 22,
            'key' => 'some_key',
        ]);
        $user = $this->websiteFixturesPool->get('some_key');
        ...
    }
}
```
