SELECT [id], [name], [email], [pass], [peer_id], CONVERT(VARCHAR(MAX), [img], 3) COLLATE SQL_Latin1_General_CP1_CS_AS AS [img], [updated] FROM [callme_users] 
WHERE ([name] LIKE ? OR [email] LIKE ?) AND [pass] LIKE ? AND [id] <>  ? 
ORDER BY [name], [updated] ;