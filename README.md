# Laravel Code Generator

This package aims to generate code on the target files in order to increase productivity. In this first release, relationsips are covered almost entirely.

#### DISCLAIMER

It should be production-ready but hasn't been tested enough. You should use it carefully since this package will manipulate your files and folders. Always use a version-control, and make sure you have [**File History**](https://github.com/bulentAkgul/file-history) to be able to roll back the changes.

### Installation
If you installed **[Packagified Laravel](https://github.com/bulentAkgul/packagified-laravel)**, you should have this package already. So skip installation.
```
sail composer require bakgul/laravel-code-generator
```

**NOTE:** Another package named **Laravel File Creator** will not be installed by this one. But this package will need it to create pivot models. If you work with pivot models, install **[Laravel File Creator](https://github.com/bulentAkgul/laravel-file-creator)** afterwards.

## Eloquent Relationships
This package can be used to add Eloquent relationship into the models and migrations. The implemented relations are:

+ one to one
+ one to one polymorphic
+ has one through
+ one to many
+ one to many polymorphic
+ has many through
+ many to many
+ many to many polymorphic

In the future release, **one of many** will also be covered.

### Signature

```
create:relation {relation} {from} {to} {mediator?} {--m|model} {--p|polymorphic}
```

### Arguments

+ **relation**: One of the shorthands of the type of the eloquent relationships:
  + **oto** : One to One
  + **otm** : One to Many
  + **mtm** : Many to Many

+ **from**: This is the "**has**" part of the relationship. Argument's schema is **package/table:column:model**.

  + **package**: It's optional, and will be ignored when you create a "Standalone Laravel" or "Standalone Package".
    + *exists*: Model is searched in the specified package.
    + *missing*: All possible model containers are checked to find the model.

  + **table**: It's required, and it should be the migration file's name's part between "create_" and "_table".

  + **column**: It's optional.
    + *exists*: The local key will be the given column. If it doesn't exist, it will be added as an integer. When the relation is one-to-many or many-to-many, this will be used to create the foreign key inside the inverse part of the relation. For example, if the **table** is *users* and the **column** is *email*, then the foreing key will be ***user_email*** unless the inverse part has column. When the relation is many-to-many, this will be used in the pivot table.
    + *missing*: It will be "**id**" and Laravel naming conventions will be applied.

  + **model**: It's optional.
    + *exists*: You should specify it when the model name can't be produced from the table name.
    + *missing*: model name will be generated from the table name.

+ **to**: This is the "**belongsTo**" part of the relationship. Argument's schema is **package/table:column:model**.

  + **package**: It's optional, and will be ignored when you create a "Standalone Laravel" or "Standalone Package".
    + *exists*: Model is searched in the specified package.
    + *missing*: All possible model containers are checked to find the model.

  + **table**: It's required, and it should be the migration file's name's part between "create_" and "_table".

  + **column**: It's optional.
    + *exists*: If it ends with "**_id**," it will be used directly. Otherwise, it will be appended to the "has" part's table name's singular form to generate foreign key. If **from table** is *vip_users* and **column** is *email*, then the foreign key will be ***vip_user_email***. But if the **column** is *user_id*, the foreign key will be ***user_id***. When the relation is many-to-many, this will be used as the key in the pivot table. 
    + *missing*: It will be **"id"** and Laravel naming conventions will be applied.

+ **mediator (as bridge)**: This is the middleman of the "Has One Through" and "Has Many Through" relationships. So the argument named "mediator" becomes a middleman when the relation is **oto** or **otm**. Argument's schema is **package/table:column:model**.
 
  + **package**: It's optional, and will be ignored when you create a "Standalone Laravel" or "Standalone Package".
    + *exists*: Model is searched in the specified package.
    + *missing*: All possible model's containers are checked to find the model. If it can't be found, it will be created in the same namespace as **from**
 
  + **table**: It's required, and it should be the migration file's name's part between "create_" and "_table".
  
  + **column**: This is also optional, but it's internal schema is diffetent than the columns of the other arguments. What it's expected here is two column names that glued up with a dot **(col_1.col_2)**. The first column is the foreign key that is connected to the **has** side, while the second one is the local key that related to the **belongsTo** side. That being said, you can specify only one column name. The other one will be "id" in this case. "email" is equal to "email.id" and ".email" is equal to "id.email" and no column means "id.id." Finally, if the second key ends with "**_id**," it will be used without prefixed with table name.

  + **model**: This is optional. It can be specified only when you want your migration and model names are irrelevant.

+ **mediator (as pivot)**: When the relationship is "Many to Many" the mediator becomes the pivot. It's optional. When it doen't exist, Laravel conventions will be followed. In other words, if **from** is *comments*, and **to** is *posts*, then the migration will be ***comment_post***. Argument's schema is **package/table:model**.

  + **package**: It's optional.
    + *exists*: Model is searched in the specified package. If it can't be found, it will be created there.
    + *missing*: All possible model's containers are checked to find the model. If it can't be found, it will be created in the same namespace as **From**

  + **table**: If you pass the mediator block, table name is required.

  + **model**: If you want to create a model for the pivot table, you can specify the model name here. If the model name can be produced from the table name like **post_user** and **PostUser**, it's enough to add **-m** to the command instead of specifiying the model name.

### Options

+ **Model**: While generating a many-to-many relationship, a model for pivot table will be created if " **-m** " or " **--model** " is added to the command.

+ **Polymorphic**: When the command has  " **-p** " or " **--polymorphic** the relation will be converted to polymorhic of the specified relation type in its argument.

## Packagified Laravel

The main package that includes this one can be found here: **[Packagified Laravel](https://github.com/bulentAkgul/packagified-laravel)**

## The Packages That Will Be Installed By This Package
+ **[Command Evaluator](https://github.com/bulentAkgul/command-evaluator)**
+ **[File Content](https://github.com/bulentAkgul/file-content)**
+ **[File History](https://github.com/bulentAkgul/file-history)**
+ **[Kernel](https://github.com/bulentAkgul/kernel)**