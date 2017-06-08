<?php
return <<<'JSON'
{
    "tables": [
        {
            "name": "queued_tasks",
            "columns": {
                "task_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "task_id",
                    "type": "BIGINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_action": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "task_action",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "task_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_priority": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "5",
                    "length": 1,
                    "name": "task_priority",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_next_start": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "task_next_start",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_running": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "task_running",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_last_start": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "task_last_start",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_tag": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "latin1_swedish_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "task_tag",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_fails": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 2,
                    "name": "task_fails",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_blog_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "",
                    "length": 11,
                    "name": "task_blog_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "task_completed": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "task_completed",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "task_id"
                    ]
                },
                "task_priority": {
                    "type": "key",
                    "name": "task_priority",
                    "length": [
                        null
                    ],
                    "columns": [
                        "task_priority"
                    ]
                },
                "task_next_start": {
                    "type": "key",
                    "name": "task_next_start",
                    "length": [
                        null
                    ],
                    "columns": [
                        "task_next_start"
                    ]
                },
                "task_last_start": {
                    "type": "key",
                    "name": "task_last_start",
                    "length": [
                        null
                    ],
                    "columns": [
                        "task_last_start"
                    ]
                },
                "task_fails": {
                    "type": "key",
                    "name": "task_fails",
                    "length": [
                        null
                    ],
                    "columns": [
                        "task_fails"
                    ]
                }
            }
        }
    ]
}
JSON;
