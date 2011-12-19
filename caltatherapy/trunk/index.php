<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="css/main.css" media="screen" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="/js/nivo-slider/themes/default/default.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="/js/nivo-slider/nivo-slider.css" type="text/css" media="screen" />
        <!-- <link rel="stylesheet" href="style.css" type="text/css" media="screen" /> -->

        <script type="text/javascript" src="/js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="/js/nivo-slider/jquery.nivo.slider.pack.js"></script>

        <script type="text/javascript">

            // Google Analytics
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-25138124-1']);
            _gaq.push(['_setDomainName', '.caltatherapy.com']);
            _gaq.push(['_trackPageview']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();

            

        </script>
    </head>
    <body>

        <!--
        <div id="background">
            <img height="1800" width="1800" alt="Calta Therapy" src="images/clouds_background_reduced.jpg">
        </div>
        -->
        <div id="banner" class="banner">
            <span id="bannerTitle" class="bannerTitle">
                MaryAnn Calta, <span id="qualifications" class="qualifications">LCSW, LHMP, CMSW, M.Div</span>
            </span>
            <br />
            <span id="bannerFoot" class="bannerFoot">
                Psychotherapy <span class="diamond">&#x25C6;</span> Spiratual Direction
            </span>

            <span id="leaf" class="leaf"><img src="images/fall-leaf1.png" /></span>
        </div>
        <div id="tabsHolder" class="tabsHolder">
            <span id="tabAbout" class="tabs" onClick="show('about')">
                About
            </span>
            <span id="tabOffice" class="tabs" onClick="show('office')">
                Office
            </span>
            <span id="tabPayments" class="tabs" onClick="show('payments')">
                Payments
            </span>
            <span id="tabForms" class="tabs" onClick="show('forms')">
                Forms
            </span>
        </div>

        <div id="about" class="contentWrapper">
            <div id="content" class="content">
                <img src="images/black_x.png" class="blackx" alt="Close" id="black_x" onClick="hide('about')"/>
                <div id="picColumn" class="picColumn">
                    <div class="macImage" align="center">
                        <img src="images/ma_calta_yellow.jpg" class="macImage" alt="MaryAnn Calta" id="macImage" />
                    </div>
                    <br />
                    <div id="addressText" class="addressText">
                        <div id="addressHeaderText" class="addressHeaderText">
                            <p>The Historic Paxton Building</p>
                        </div>
                        <p>1403 Farnam St <span class="diamond">&#x25C6;</span> Suite 215
                        <br />
                        Omaha, NE 68102
                        <br />
                        Office: 402.393.0642
                        <br />
                        Fax: 402.391.2641
                        <br />
                    </div>
                </div>
                <div id="textColumn" class="textColumn">
                    <p>
                        &nbsp;&nbsp;&nbsp;&nbsp;MaryAnn Calta LCSW, LHMP, CMSW, M.Div, has more than fifteen years experience as a psychodynamic psychotherapist (psychotherapy) using Jungian, developmental, and other psychodynamic theories. She is trained in systems theory and dynamics, including conflict resolution. Her areas of expertise include grief work, recovery from trauma, depression, anxiety, and sexual violence, including clergy sexual abuse and misconduct. Consultation is offered to professionals in various fields.
                    </p>
                    <p>
                        &nbsp;&nbsp;&nbsp;She began her work in social services in 1972 in the Department of Human Services in California. Through her 39 years of journeying with others, she has become a specialist at integrating mind, body, and spirit for holistic health. Besides her credentials in clinical social work, (Certified Master Social Worker, Licensed Clinical Social Worker, and Licensed Mental Health Practitioner) she is a certified spiritual director, Reiki II practitioner, and is an ordained Presbyterian minister (M.Div).
                    </p>
                    <p>
                        &nbsp;&nbsp;&nbsp;&nbsp;Traditional “Christian” therapy is NOT used in her practice; rather, Ms. Calta draws from her extensive background of working in interfaith settings as clergy and secular clinical settings as a hospital chaplain and psychotherapist. Incorporating any spiritual issues into the therapy is done strictly at the client’s request. She welcomes people of all faith traditions.
                    </p>
                    <p>
                        &nbsp;&nbsp;&nbsp;&nbsp;Ms. Calta believes the common boundaries of spiritual and mental health intersect in our lives as we search for meaning and wholeness, regardless of faith background. Helping people make meaning out of their lives and facilitating psychological healing are her gifts. 
                    </p>
                </div>
            </div>
        </div>

        <div id="office" class="contentWrapper">
            <div id="content" class="content">
                <img src="images/black_x.png" class="blackx" alt="Close" id="black_x" onClick="hide('office')"/>
                <div id="picColumn" class="picColumn">

                    <!-- Slide show of office -->
                    <div class="slider-wrapper theme-default">
                        <div class="ribbon"></div>
                        <div id="slider" class="nivoSlider">
                            <img src="/images/office1.jpg" alt="" />
                            <img src="/images/office2.jpg" alt="" />
                            <img src="/images/office3.jpg" alt="" />
                            <img src="/images/office4.jpg" alt="" title="#htmlcaption" />
                        </div>
                        <div id="htmlcaption" class="nivo-html-caption">
                            Lobby of the historic <a href="http://www.thepaxton.com/downtown-omaha-condos/">Paxton building</a>.
                        </div>
                    </div>

                    <br />
                    <div id="addressText" class="addressText">
                        <div id="addressHeaderText" class="addressHeaderText">
                            <p>The Historic Paxton Building</p>
                        </div>
                        <p>1403 Farnam St <span class="diamond">&#x25C6;</span> Suite 215
                        <br />
                        Omaha, NE 68102
                        <br />
                        Office: 402.393.0642
                        <br />
                        Fax: 402.391.2641
                        <br />
                    </div>
                </div>
                <div id="textColumn" class="textColumn">
                    <p>Office is conveniently located in the Historic Paxton Building in downtown Omaha at the Southwest Corner of 14th and Farnam Streets.</p>
                    <p>Metered parking is available on either Farnam or 14th Streets.</p>

                    <a href="http://maps.google.com/maps?q=1403+Farnam+St,omaha,+ne&hl=en&ll=41.25715,-95.935133&spn=0.002762,0.006968&sll=37.0625,-95.677068&sspn=47.349227,114.169922&vpsrc=6&hnear=1403+Farnam+St,+Omaha,+Nebraska+68102&t=m&z=18" 
                       target="_blank" />
                        <img src="/images/map.png" />
                    </a>
                </div>
            </div>
        </div>

        <div id="payments" class="contentWrapper">
            <div id="content" class="content">
                <img src="images/black_x.png" class="blackx" alt="Close" id="black_x" onClick="hide('payments')"/>
                <div id="picColumn" class="picColumn">
                    <br />
                    <div id="addressText" class="addressText">
                        <div id="addressHeaderText" class="addressHeaderText">
                            <p>The Historic Paxton Building</p>
                        </div>
                        <p>1403 Farnam St <span class="diamond">&#x25C6;</span> Suite 215
                        <br />
                        Omaha, NE 68102
                        <br />
                        Office: 402.393.0642
                        <br />
                        Fax: 402.391.2641
                        <br />
                    </div>
                </div>
                <div id="textColumn" class="textColumn">
                    <p>Most major insurances are accepted, as well as major credit cards.</p>
                    <div align="center">
                        <table>
                            <tr>
                                <td align="center" vAlign="center"> 
                                    <img src="images/visa-logo.jpg" alt="Visa" />
                                </td>
                                <td align="center" vAlign="center"> 
                                    <img src="images/mastercard-logo.jpg" alt="Mastercard" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>


    </body>
</html>

<script language="JavaScript">

$(document).ready(function() {
    hide('all');
});

$(window).load(function() {
    $('#slider').nivoSlider();
});

function hide(category)
{
    if(category == "all")
    {
        $('#about').hide('fast');
        $('#office').hide('fast');
        $('#payments').hide('fast');
        $('#forms').hide('fast');

        $('#tabAbout').addClass("tabs");
        $('#tabAbout').removeClass("tabSelected");
        $('#tabOffice').addClass("tabs");
        $('#tabOffice').removeClass("tabSelected");
        $('#tabPayments').addClass("tabs");
        $('#tabPayments').removeClass("tabSelected");
        $('#tabForms').addClass("tabs");
        $('#tabForms').removeClass("tabSelected");
    }
    else if(category == "about")
    {
        $('#about').hide('fast');
        $('#tabAbout').addClass("tabs");
        $('#tabAbout').removeClass("tabSelected");
    }
    else if(category == "office")
    {
        $('#office').hide('fast');
        $('#tabOffice').addClass("tabs");
        $('#tabOffice').removeClass("tabSelected");
    }
    else if(category == "payments")
    {
        $('#payments').hide('fast');
        $('#tabPayments').addClass("tabs");
        $('#tabPayments').removeClass("tabSelected");
    }
    else if(category == "forms")
    {
        $('#forms').hide('fast');
        $('#tabForms').addClass("tabs");
        $('#tabForms').removeClass("tabSelected");
    }
}

function show(category)
{
    hide("all");

    if(category == "about")
    {
        $('#about').show('slow');
        $('#tabAbout').addClass("tabSelected");
        $('#tabAbout').removeClass("tabs");
    }
    else if(category == "office")
    {
        $('#office').show('slow');
        $('#tabOffice').addClass("tabSelected");
        $('#tabOffice').removeClass("tabs");
    }
    else if(category == "payments")
    {
        $('#payments').show('slow');
        $('#tabPayments').addClass("tabSelected");
        $('#tabPayments').removeClass("tabs");
    }
    else if(category == "forms")
    {
        $('#forms').show('slow');
        $('#tabForms').addClass("tabSelected");
        $('#tabForms').removeClass("tabs");
    }


}

/*
    $('#tab1').click(function() {
      $('#tab1').fadeOut('slow', function() {
        // Animation complete.
      });
    });
*/

</script>
