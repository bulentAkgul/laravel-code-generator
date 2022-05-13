# Laravel Code Generator

The purpose of this package is to generate code on the target files in order to increase productivity.

### Installation
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

## Signature
```
sail artisan create:relation {relation} {from} {to} {mediator?} {--m|model} {--p|polymorphic}
```
## Schemas
+ **from**: package/table:column:model
+ **to**:   package/table:column:model
+ **mediator (as through)**: package/table:column:model
+ **mediator (as pivot)**: package/table:model
### Expected Inputs
+ **Relation**: One of the shorthands of the type of the eloquent relations:
  + **oto** : One to One
  + **otm** : One to Many
  + **mtm** : Many to Many
+ **Model**: While generating a many-to-many relationship, a model for pivot table will be created if " **-m** " or " **--model** " is added to the command.
+ **Polymorphic**: When the command has  " **-p** " or " **--polymorphic** the relation will be converted to polymorhic of the specified type in the argument named "relation."
### Schemas and Details of From, To, and Mediator
+ **From**: This is the "***has***" part of the relationship.

  + **Schema**: package/table:column:model




<br>

  + **Details**:
    + **package**: It's optional.
      + *exists*: Model is searched in the specified package.
      + *missing*: All possible model containers are checked to find the model.
    + **table**: It's required, and it should be the migration file's name's part between "create_" and "_table".
    + **column**: It's optional.
      + *exists*: The local key will be the given column. When the relation is many-to-many, this will be used in the pivot table.
      + *missing*: By default it is **"id"** and Laravel naming conventions will be applied.
    + **model**: It's optional.
      + *exists*:
      + *missing*: 
+ **To**: This is the "***belongsTo***" part of the relationship.
  + **Schema**: package/table:column:model
  + **Details**:
    + **package**: It's optional.
      + *exists*: Model is searched in the specified package.
      + *missing*: All possible model's containers are checked to find the model.
    + **model**: It's required.
    + **column**: It's optional.
      + *exists*: The foreing key will be the given column.
      + *missing*: By default it's generated based on the model name of the "From" or "Mediator" (if relationship is "through"). For example, if the model is User, then the column will be "user_id"
+ **Mediator (as through)**: This is the middleman of the "Has One Through" and "Has Many Through" relationships. So the mediator becomes a middleman when the relation is *oto* or *otm*.
  + **Schema**: package/model:column
  + **Details**:
    + **package**: It's optional.
      + *exists*: Model is searched in the specified package. If it can't be found, it will be created there.
      + *missing*: All possible model's containers are checked to find the model. If it can't be found, it will be created in the same namespace as **From**
    + **model**: It's required.
    + **column**: It's optional.
      + *exists*: The foreign key will be the given column.
      + *missing*: By default it's generated based on the model name of the "From." For example, if the model is UserDetails, then the column will be "user_detail_id"
+ **Mediator (as pivot)**: When the relationship is "Many to Many" the mediator becomes the pivot. It's optional. When it doen't exist, Laravel conventions will be followed. In other words, if From is "comments", and To is "posts", the migration will be comment_post.
  + **Schema**: package/table:model
  + **Details**:
    + **package**: It's optional.
      + *exists*: Model is searched in the specified package. If it can't be found, it will be created there.
      + *missing*: All possible model's containers are checked to find the model. If it can't be found, it will be created in the same namespace as **From**
    + **table**: If you don't want to use the naming convention of Laravel, you can specify a table name.
    + **model**: If you want to create a model for the pivot table, and if you want to name it differently, you can specify the model name here.

## Examples

```
sail artisan create:relation oto users phones 
```
```
sail artisan create:relation oto users phones phone-book
```
```
sail artisan create:relation oto users phones -p
```






