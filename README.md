#QPM [![Build Status](https://secure.travis-ci.org/Comos/qpm.png)](http://travis-ci.org/Comos/qpm)

QPM全名是 Quick(or Q's) Process Management Framework for PHP.
PHP 是强大的web开发语言，以至于大家常常忘记PHP 可以用来开发健壮的命令行（CLI）程序以至于daemon程序。
而编写daemon程序免不了与各种进程管理打交道。QPM正是为简化进程管理而开发的类库。

QPM是一个专门针对*nix CLI编程的框架，不可用于Windows环境和CGI编程。

QPM 目前包括4个主要的子模块：
* Process 基础进程管理,包括fork的面向对象封装；
* Supervision 进程监控,实现了OneForOne、MultiGroupOneForOne 和 TaskFactoryMode 三种模式；
* Pid 支持PID文件管理；
* Log 实现了用于测试的简易文件日志，同时支持接入 Psr 标准的日志实现，例如 Monolog。

examples目录下有若干使用的示例，tests是测试用例所在的目录。

QPM也支持通过Composer安装:```composer require comos/qpm```

也可通过其他方式下载并部署QPM，参考[安装和使用QPM](https://github.com/Comos/qpm/wiki/安装和使用QPM)。

QPM 运行时，必须使用autoloader，强烈建议使用Composer自带的autoloader，否则需要自行注册autoload回调，可参考：[autoload.php](https://github.com/Comos/qpm/blob/master/bootstrap.php)

通过[Wiki](https://github.com/Comos/qpm/wiki) 可以获取更多信息。

QPM 最新版本是v1.0,与 之前的v0.3有较大的变化，使用时请留意兼容性问题，如果没有修改代码的计划，请不要从v3.0升级到v1.0。

----------------------

QPM’s full name is Quick Process Management Framework in PHP.

PHP is so powerful in web development, that people always forget that it could be used to write strong CLI programs, even the daemon programs.
The process management is just the core of daemon programming. QPM is such a library to simplify the process management.
QPM is a CLI programming framework based *nix systems. It cannot be used in Windows and CGI environment.

There're four main packages:

* Process, the basic process management, includes the OO style encapsulation of pcntl_fork.
* Supervision, the process supervisor, supports one-for-one mode, multi-group-one-for-one mode and task factory mode.
* Pid manages the PID file to prevent the the daemon is started duplicately.
* Log includes a file based simple Logger as the test purpose. The Logger supports PSR-3, so you can connect QPM to any implementer of PSR-3, such as Monolog.

We provide rich usage samples in ‘examples’ directory.

The library is covered by unit tests well, all the tests are in ‘tests’ directory.

You can install QPM by composer:

```

composer require comes/qpm

```
Notice: the lastest verison v1.0 is not compatible with v0.3.
If you're using v0.3 or earlier versions, don't upgrade the library without refactoring and test.

The other ways to get and use QPM, see [[Getting Started]].