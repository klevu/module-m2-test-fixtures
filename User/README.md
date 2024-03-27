# User Fixtures

Add Test fixtures based on https://github.com/tddwizard/magento2-fixtures

Fixture Defaults
```php
[
    'firstname' => 'Admin',
    'lastname' => 'User',
    'email' => 'admin_user@klevu.com',
    'username' => 'admin',
    'password' => 'P@aS5w0rD',
    'key' => 'test_user',
]
```
Build with defaults
```php
$userBuilder = UserBuilder::addUser();
$userBuilder->build();
```
Build with custom values
```php
$userBuilder = UserBuilder::addUser();
$userBuilder->withFirstName('Example');
$userBuilder->withLastName('User');
$userBuilder->withUserName('ExampleUser');
$userBuilder->withEmail('user@example.com');
$userBuilder->withPassword('&@£HE(hw0duj2');
$userBuilder->build();
```
Add user to fixtures pool and tag it for easy recall.
The tag/key does not need to match the store code
```php
$userBuilder = UserBuilder::addUser();

$this->userFixturesPool->add(
    $userBuilder->build(),
    'admin_user'
);
```
Retrieve from fixtures pool using tag/key
```php
$store = $this->storeFixturesPool->get('admin_user');
```

Data Fixture to create new User
```php
use UserTrait;

$this->createUser([
    'firstname' => 'Joe',
    'lastname' => 'Bloggs',
    'email' => 'admin@example.com',
    'key' => 'some_key',
]);
```

### Example with UserTrait

```php
use Klevu\TestFixtures\User\UserBuilder;
use Klevu\TestFixtures\User\UserFixturesPool;
use Klevu\TestFixtures\User\UserTrait;
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    use UserTrait;
     
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->userFixturesPool = $this->objectManager->create(UserFixturesPool::class);
    }

    protected function tearDown(): void
    {
        $this->userFixturesPool->rollback();
    }
    
    public function testSomething_withStoreDefaults(): void
    {
        $this->createUser();
        $store = $this->userFixturesPool->get('test_user');
        ...
    }
    
    public function testSomething_withStoreOverrides(): void 
    {
        $this->createUser([
            'firstname' => 'Joe',
            'lastname' => 'Bloggs',
            'email' => 'admin@example.com',
            'key' => 'some_key',
        ]);
        $user = $this->userFixturesPool->get('some_key');
        ...
    }
}
```
