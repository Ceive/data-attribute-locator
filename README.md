Ceive.Data.AttributeLocator
===========================
Path Locator - Локатор пути - Это специальное средство, который на основе переданного пути
(путь через вложенности объектов данных), производит его прохождение , тем самым достигая конечного ключа
и в итоге получая значение из реальной вложенной структуры данных,  находящегося в указанном пути.



Example paths:

    {user.profile.name}
    {user.profile.contacts:first}

В дополнение:


locator.behaviour.setPatternDefaultValue('user.firstname', 'DEFAULT_VALUE', PATH_REMAINS);

locator.behaviour.setPathDefaultValue('user.firstname', 'DEFAULT_VALUE');
locator.behaviour.setClassMemberDefaultValue('App\\Model\\User', 'username', 'DEFAULT_VALUE');
locator.behaviour.setClassRelativeDefaultValue('App\\Model\\User', 'profile.firstName', 'DEFAULT_VALUE');



locator.behaviour.setPatternFilter('user.firstname', function($value){return $value}, PATH_REMAINS);

locator.behaviour.setPathFilter('user.firstname', function($value){return $value});
locator.behaviour.setClassMemberFilter('App\\Model\\User', 'username', function($value){return $value});
locator.behaviour.setClassRelativeFilter('App\\Model\\User', 'profile.firstName', function($value){return $value});