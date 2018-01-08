# WangCMF内容管理框架

## 简介
WangCMF是一个基于ThinkPHP5开发的CMF(内容管理框架)，为了方便项目开发而生，项目集成了一个常见用户后台，和一个模拟下单的网站前台，包含了常见的功能模块，无需重复开发。框架开发使用到了ThinkPHP5、Bootstrap框架，后台使用了AdminLTE模板技术。其中包含以下功能：
* 用户系统：包含基本的用户登录、注册和管理功能。
* 权限系统：基于RBAC的权限管理系统。
* 系统管理：基本的配置管理。
* Crontab计划任务管理：在管理后台可以直接添加相应的计划任务，系统自动执行。
* 队列商品抢购处理：为了防止在高并发请求下，对于MySQL数据库压力，框架集成了基于Redis发布订阅队列机制的商品抢购模块。
## 最低要求

框架运行的基本要求与ThinkPHP5框架的需求，基本保持一致。不过Redis的商品抢购，需要本地安装Redis和PHP的Redis扩展。

> * PHP >= 5.4.0
> * PDO PHP Extension
> * MBstring PHP Extension
> * CURL PHP Extension
> * MySQL >= 5.6.0
> * Redis PHP Extension
> * Redis Server/Cli

## 安装说明

项目根目录下的install.sql为数据库结构文件，导入即可。其他的结构和访问形式可以参考ThinkPHP5。

## 特别感谢

* https://github.com/top-think/framework
* https://github.com/twbs/bootstrap
* https://github.com/almasaeed2010/AdminLTE
* https://github.com/antirez/redis
* https://ckeditor.com/