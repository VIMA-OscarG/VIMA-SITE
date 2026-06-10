class TraducirClassMaster {
    constructor() {
        this.traduccionesObject = {};
        this.currentLanguage = this.getCurrentLanguage(); 
        if (this.currentLanguage !== 'en') {
            this.traduccionesPromise = this.initialize();
        } else {
            this.traduccionesPromise = Promise.resolve(); 
        }
    }

    async initialize() {
        try {
           
            const traducciones = await this.getTraductionsFile();
            const data = await traducciones.json();
            this.setTraducciones(data);
        } catch (error) {
            console.error('Error al obtener las traducciones:', error);
        } finally {
            console.log('traducciones cargadas');
        }
    }

    async getTraductionsFile() {
        const baseUrl = window.location.origin;
        const traductionFileUrl = `${baseUrl}/traducciones/profile-menu/traducciones.json`;

        let traductionFile = await fetch(traductionFileUrl, {
            headers: {
                "Content-Type": "application/json",
            },
        });
        return traductionFile;
    }

    setTraducciones(traducciones) {
        this.traduccionesObject = traducciones;
    }

    async traducir(texto) {
        if (this.currentLanguage === 'en') {
           
            return texto;
        }
        await this.traduccionesPromise; 
        return this.traduccionesObject[texto] || texto;
    }

    getCurrentLanguage() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('lang') || 'en'; // 'en' es el idioma por defecto
    }

    ajustarSwitcherMemberArea (switcherTop, actualLanguage, currentSection) {

        let switchers = document.getElementsByClassName(switcherTop);
       // console.log(switchers);
      //  console.log(currentSection);
      //  console.log(actualLanguage);
        
        let currentUrl = window.location.href;
        let url = new URL(currentUrl);
        url.searchParams.delete('lang');
        let mismoLinkNoLanguage = url.href;

        Array.from(switchers).forEach(switcher => {

            let switcherLink = switcher.querySelector('a.raven-menu-item');
            if(this.currentLanguage !== 'en'){
                
                switcherLink.setAttribute("href", mismoLinkNoLanguage);
            }else{
                url.searchParams.append('lang', 'es');
                mismoLinkNoLanguage = url.href;
                switcherLink.setAttribute("href", mismoLinkNoLanguage);
            }
            
           // console.log(switcherLink);
        });
        
        


    }

}//termia la clase
let traduccionesClass = new TraducirClassMaster();

document.addEventListener("DOMContentLoaded", function() {
    
    let settingTradMenu = traduccionesClass.ajustarSwitcherMemberArea('wpml-ls-slot-34', 'en', 'preferred-rentals-request');


    var links = document.querySelectorAll('a[href*="/member-area/"]');

        links.forEach(function(link) {
            var href = link.getAttribute('href');
        
            if (href.match(/\?lang=es\/.+/)) {
            
                var newHref = href.replace(/\?lang=es\/(.+)/, '/$1?lang=es').replace(/([^:])\/\//g, '$1/');
                link.setAttribute('href', newHref);
            } else if (href.match(/\?lang=es\/?$/)) {
                
                var newHref = href.replace(/\?lang=es\/?$/, '?lang=es');
                link.setAttribute('href', newHref);
            }
        });

        let menuItems = document.querySelectorAll('.jet-profile-menu__item-link');
        
        menuItems.forEach(function(item) {
           
            if (traduccionesClass.getCurrentLanguage !== 'en') {
               
                let textoMenuOrig = item.textContent.trim();
                traduccionesClass.traducir(textoMenuOrig).then(traduccion => {
                    // label.textContent = ;
                    item.textContent = traduccion;
                  //  console.log(traduccion);
                  //  console.log(item);
            
                  });
            }
        });


        console.log('ajustes de traducci&oacute;n aplicados');

});