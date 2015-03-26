# Examples
包括了QPM的各个典型应用场景。
所有的程序均在命令行下执行, 例如:
```
php daemon.php
```

## Process 基本进程管理
* simple_master_workers.php	master-worker 模式程序的例子。
* daemon.php	使用QPM 编写daemon程序。
* fork.php	fork 的使用示例。
* to_background.php 将进程转入后台的示例。

## Supervisor 进程监控（进程树管理）
* multi_group_supervision.php	MultiGroupOneForOne模式进程监控（进程树管理）的使用示例。
* one_for_one_supervision.php	OneForOne模式进程监控（进程树管理）的使用示例。
* spider_task_factory.php	TaskFactoryMode模式进程监控（进程树管理）的使用示例。spider_task_factory_data.txt是其数据文件。
* task_factory.php TaskFactoryMode模式进程监控（进程树管理）的使用示例。
* handle_timeout_gentlely.php	OneForOne模式优雅超时的例子。
* handle_timeout_gentlely_in_task_factory.php	TaskFactoryMode 优雅超时的例子。

## PID PID文件管理
* pid_check.php	和 pid_main.php	基于qpm\pid\Manager 管理和使用PID文件。

## Log 日志
* use_log.php 使用日志的例子。
* use_monolog.php 使用 monolog的例子，需要使用composer 安装 monolog