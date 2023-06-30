# ORM
 A PHP 8.2+ Attribute Based ORM for Object/Entity/DTO. Largely inspired by Java SpringBoot annotation based ORM. 

 PHP introduced it's own in-built annotation called PHP atttribute. Having used the Java springboot in a couple of project and found out a similar ORM can be implemented in PHP for easy injection and validation of object while also implementing complex relation mapping easily.

 Hey, the good news here is if you're once upon a time a Java SpringBoot developer and looking to switch to PHP, this is the best ORM for you. I will be launching the official documentation website soon. For now, here is a quick start:

 ## Dbal Connection
```
Emma\ORM\Connection\Connection::getInstance()->connect([]);
//This accept the Dbal ConnectionProperty or array of connection details.
```
See: https://github.com/debascoguy/Dbal for more information on connnection manager.

## Quick Example Application: To the main fun:

## Entity: AppUser

```
<?php

use Emma\ORM\Attributes\Entity\Entity;
use Emma\ORM\Attributes\Entity\Identifier\PrimaryKey;
use Emma\ORM\Attributes\Entity\Property\Column;
use Emma\ORM\Attributes\Relationships\JoinColumn;
use Emma\ORM\Attributes\Relationships\JoinColumnValue;
use Emma\ORM\Attributes\Relationships\JoinTable;
use Emma\ORM\Attributes\Relationships\OneToMany;
use Emma\ORM\Attributes\Relationships\OneToOne;
use Emma\Validation\Converter\DateTimeFormat;

#[Entity("app_user")]
class AppUser
{
    /**
     * @var int|null
     */
    #[PrimaryKey]
    #[Column('id')]
    protected ?int $id = null;

    /**
     * @var string|null
     */
    #[Column('email')]
    protected ?string $email = null;

    /**
     * @var string|null
     */
    #[Column('first_name')]
    protected ?string $first_name = null;

    /**
     * @var string|null
     */
    #[Column('last_name')]
    protected ?string $last_name = null;

    /**
     * @var string|null
     */
    #[Column('country')]
    protected ?string $country = null;

    /**
     * @var string|null
     */
    #[Column('zipcode')]
    protected ?string $zipcode = null;

    /**
     * @var string|null
     */
    #[Column('state')]
    protected ?string $state = null;

    /**
     * @var string|null
     */
    #[Column('city')]
    protected ?string $city = null;

    /**
     * @var string|null
     */
    #[Column('address')]
    protected ?string $address = null;

    /**
     * @var int|null
     */
    #[Column('img')]
    protected ?int $img = null;

    /**
     * @var string|null
     */
    #[Column('password')]
    protected ?string $password = null;

    /**
     * @var \DateTime|null
     */
    #[DateTimeFormat]
    #[Column('time_created')]
    protected ?\DateTime $time_created = null;

    /**
     * @var boolean
     */
    #[Column('account_expired')]
    protected bool $account_expired = false;

    /**
     * @var boolean
     */
    #[Column('account_locked')]
    protected bool $account_locked = false;

    /**
     * @var boolean
     */
    #[Column('credentials_expired')]
    protected bool $credentials_expired = false;

    /**
     * @var boolean
     */
    #[Column('is_deleted')]
    protected bool $is_deleted = false;

    /**
     * @var array
     */
    #[OneToMany(AppRole::class)]
    #[JoinTable(
        'app_users_roles',
        [new JoinColumn('user_id', 'id')],
        [new JoinColumn('role_id', 'id')],
    )]
    protected array $roles = [];

    /**
     * @var AppDocument|null
     */
    #[OneToOne(AppDocument::class)]
    #[JoinTable(
        'app_user_document',
        [new JoinColumn('user_id', 'id')],
        [
            new JoinColumn('document_id', 'id'),
            new JoinColumnValue('doc_type', DocType::PROFILE_IMAGE)
        ],
    )]
    protected ?AppDocument $profileImage = null;


    /**
     * AppUser constructor
     */
    public function __construct() 
    {
        $this->setTimeCreated(new \DateTime("now"));
    }
//... you can create the setter/getter if you want to. it's optional.
}
```
## Repository
```
<?php

namespace Emma\Modules\Api\Model\Repository;

use Emma\Dbal\QueryBuilder\Constants\FetchMode;
use Emma\Dbal\QueryBuilder\Constants\QueryType;
use Emma\Dbal\QueryBuilder\Expressions\WhereCompositeCondition;
use Emma\Dbal\QueryBuilder\Expressions\WhereCondition;
use Emma\Dbal\QueryBuilder\Services\CriteriaHandler;
use Emma\Modules\Api\Model\Entity\AppUser;
use Emma\ORM\Attributes\Repository\Repository;
use Emma\ORM\Repository\CrudRepository;

/**
 * Example of Anonymouse Functions Available
 * ======================================
 * @method AppUser[] findByEmail($email);
 * @method AppUser findOneByEmail($email);
 * @method AppUser findOneByEmailAndPassword($email, $password);
 * @method int existsByEmail($email)
 */
#[Repository(AppUser::class)]
class AppUserRepository extends CrudRepository
{
    /**
     * AppUserRepository constructor
     */
    public function __construct() 
    {
        parent::__construct(AppUser::class);
    }
    
    /**
    ... Create personal use-case functions as needed aside form the provided CRUD functions and Supported Annonymous functions available.
     */
}
```
That's the main gist of it. Start using your Repository as desired. It is recommended to use 
```https://github.com/debascoguy/Di``` for Auto-Injecting the Repo into your Services and/or Controller and/or Middleware Service/Containers. The Di is already installed as dependency for this ORM. Enjoy!

There are lots more of many advanced features and supports all type of relationship mapping and lots more support for annonymous function. Official documentation website is coming next month.