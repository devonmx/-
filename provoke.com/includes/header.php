<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Provoke</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="manifest" href="site.webmanifest">
    <link rel="apple-touch-icon" href="icon.png">
    <!-- Place favicon.ico in the root directory -->

    <link rel="stylesheet" href="<?=base_url;?>css/normalize.css">
    <link rel="stylesheet" href="<?=base_url;?>css/main.css?t=<?=time();?>">
</head>

<body class="home">
    <!--[if lte IE 9]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->
    <div id="loader-wrapper">
        <img src="<?=base_url;?>img/logo.png" alt="">
        <div id="loader"></div>
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
     
    </div>
    
    <div id="content">

    <header class="header">
        <div class="nav float-right">
            <div class="menu-toggle"><span class="burger"></span>  MENU</div>
        </div>

        <div class="header-actions">
            <div class="close"><span class="icon"></span> <span>MENU</span></div>
            <div class="action menu-action">
                <ul class="main-nav menu">
                    <li class=""> <a href="<?=base_url;?>" class="ajax-link active">HOME</a></li>
                    <li class=""> <a href="<?=base_url;?>about" class="ajax-link">ABOUT</a></li>
                    <li>
                        <a href="#" class="ajax-link">PHOTOS</a>
                        <ul class="">
                            <li class=""><a href="<?=base_url;?>photos#mujer" class="control" data-filter=".mujer">MUJER</a></li>
                            <li class=""><a href="<?=base_url;?>photos#hombre" class="control" data-filter=".hombre">HOMBRE</a></li>
                            <li class=""><a href="<?=base_url;?>photos#pareja" class="control" data-filter=".pareja">PAREJAS</a></li>
                            <li class=""><a href="<?=base_url;?>photos#all" class="control" data-filter="all">TODO</a></li>
                        </ul>
                    </li>
                    <li class="">
                        <a href="#" class="ajax-link">VIDEOS</a>
                        <ul>
                            <li class=""> <a href="javascript:;" class="active">SOLO</a></li>
                            <li class=""> <a href="javascript:;">PAREJAS</a></li>
                        </ul>
                    </li>
                    <li class=""> <a href="javascript:;" class="ajax-link">CONTACTO</a></li>
                    <li class=""> <a href="javascript:;" class="ajax-link">INGRESAR</a></li>
                </ul>        
            </div>
            <a href="<?=base_url;?>" class="logo"><img src="<?=base_url;?>img/logo.png" alt=""></a>
        </div>
    </header>