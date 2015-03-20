#QPM [![Build Status](https://secure.travis-ci.org/Comos/qpm.png)](http://travis-ci.org/Comos/qom)

QPM全名是 Quick(or Q's) Process Management Framework for PHP.
PHP 是强大的web开发语言，以至于大家常常忘记PHP 可以用来开发健壮的命令行（CLI）程序以至于daemon程序。
而编写daemon程序免不了与各种进程管理打交道。QPM正是为简化进程管理而开发的类库。

QPM是一个专门针对*nix CLI编程的框架，不可用于Windows环境和CGI编程。

QPM 目前包括4个主要的子模块：
* Process 基础进程管理,包括fork的面向对象封装；
* Supervisor 进程监控,实现了OneForOne、MultiGroupOneForOne 和 TaskFactoryMode 三种模式；
* Pidfile 支持PID文件管理；
* Log 实现了用于测试的简易文件日志，同时支持接入 Psr 标准的日志实现，例如 Monolog。

examples目录下有若干使用的示例，tests是测试用例所在的目录。

QPM也支持通过Composer安装:```composer require comos/qpm```

也可通过其他方式下载并部署QPM，参考[安装和使用QPM](https://github.com/Comos/qpm/wiki/安装和使用QPM)。

QPM 运行时，必须使用autoloader，强烈建议使用Composer自带的autoloader，否则需要自行注册autoload回调，可参考：[autoload.php](https://github.com/Comos/qpm/blob/master/bootstrap.php)

通过Wiki[https://github.com/Comos/qpm/wiki] 可以获取更多信息。
