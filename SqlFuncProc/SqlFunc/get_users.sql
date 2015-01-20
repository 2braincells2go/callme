SELECT * FROM `callme_users` 
WHERE (`name` LIKE ? OR `email` LIKE ?) AND `pass` LIKE ? AND `id` <>  ? 
ORDER BY `name`, `updated` ;