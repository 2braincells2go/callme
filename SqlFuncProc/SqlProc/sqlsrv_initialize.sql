IF NOT EXISTS (SELECT * FROM sysobjects	WHERE name = 'callme_users'	AND xtype = 'U')
BEGIN
	CREATE TABLE [dbo].[callme_users](
		[id] [int] IDENTITY(1,1) NOT NULL,
		[name] [nvarchar](12) NULL,
		[email] [nvarchar](50) NULL,
		[pass] [nvarchar](12) NULL,
		[peer_id] [nvarchar](20) NULL,
		[img] [varbinary](MAX) NULL,
		[updated]  AS (getdate())
	) ON [PRIMARY]
END;