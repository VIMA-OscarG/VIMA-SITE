class TraducirClass {
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
            console.log('termino el initialize');
        }
    }

    async getTraductionsFile() {
        const baseUrl = window.location.origin;
        const traductionFileUrl = baseUrl + '/traducciones/prr/traducciones.json';

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

}//termia la clase


//comienzan las traduccionesObject

// Instanciar la clase TraducirClass
const traductor = new TraducirClass();

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.jet-form__field-wrap');
    const loadingDiv = document.getElementById('loading');
    const groupedCheckboxes = {};

    checkboxes.forEach(checkbox => {
        const label = checkbox.querySelector('.jet-form__field-label');
        if (label) {
            const value = label.textContent.trim();
            //console.log(value);
            let type = '';
            
            if (value.includes('Grand Solmar')) {
                type = 'Grand Solmar';
            }
            else if (value.includes("San Miguel de Allende")) {
                type = 'Amatte';
            } 
            else if (value.includes('Grand Moon')) {
                type = 'Palace Resorts';
            } 
            else if (value.includes('Corazon') || value.includes('Corazon Bayview')) {
                type = 'Cabo Villas';
                //console.log('cabo villas debería aparecer');
            } 
            else if (value.includes('4 Bedroom Villa')
                || value.includes('3 Bedroom Villa')
                || value.includes('2 Bedroom Villa Los')
                || value.includes('2 Bedroom Villa The')
                || value.includes('2 Bedroom Villa Hacienda')
                || value.includes('1 Bedroom Villa Los')
                || value.includes('4 Bedroom Vista')
                || value.includes('3 Bedroom Vista')
                || value.includes('2 Bedroom Vista')
                || value.includes('1 Bedroom Vista')) {
                type = 'Hacienda Encantada';
            } else if (value.includes('Grand Luxxe') 
            || value.includes('Grand Mayan')
            || value.includes('Nuevo Vallarta')
            || value.includes('Mayan Palace')
            || value.includes('East Cape')
            || value.includes('1')
            || value.includes('2')
            || value.includes('3')
            || value.includes('4')) {
                type = 'Vidanta';
            }
            else if (value.includes("Marival")) {
                type = 'Marival';
               // console.log(value);
            }
            else if (value.includes('Villa La Valencia')) {
                type = 'Villa Group';
            }
            else if (value.includes("Occidental Xcaret")) {
                type = 'Occidental Xcaret';
               // console.log(value);
            }
            else if (value.includes("Casa Del Mar")) {
                type = 'Casa Del Mar';
               // console.log(value);
            }
            
             
            else {
                console.log(value);
                return; // Si no coincide con ningun tipo, omitir este checkbox
            }
            
        if (traductor.currentLanguage !== 'en') {
                traductor.traducir(value).then(traduccion => {
                   // label.textContent = ;
                   label.childNodes[2].textContent = traduccion;
                   //console.log(label.childNodes[2].textContent);
           
                 });
        }
        
            if (!groupedCheckboxes[type]) {
                groupedCheckboxes[type] = [];
            }
            groupedCheckboxes[type].push(checkbox); 
        }
    });

    const destinosTipos = Object.keys(groupedCheckboxes).slice(0, 10);
    const columnContainers = [];

    for (let i = 0; i < destinosTipos.length; i++) {
        const columnContainer = document.createElement('div');
        columnContainer.classList.add('column');
        columnContainers.push(columnContainer);
    }

    destinosTipos.forEach((type, index) => {
        const groupContainer = document.createElement('div');
        groupContainer.classList.add('destination-group');

        const title = document.createElement('h3');
        title.textContent = type;
        groupContainer.appendChild(title);

        groupedCheckboxes[type].forEach(checkboxDiv => {
            groupContainer.appendChild(checkboxDiv); // Agregar el div contenedor al contenedor del grupo
        });

        const columnIndex = index;
        //console.log(index);
        //console.log(columnIndex);
        columnContainers[columnIndex].appendChild(groupContainer);
		
        
    });

    const form = document.querySelector('.jet-form__fields-group');
    if (form) {
        columnContainers.forEach(columnContainer => {
            form.appendChild(columnContainer);
            document.getElementById('contenedor_checkboxes').style.display = 'initial';
            setTimeout(() => {
                loadingDiv.style.display = 'none';
            }, 300);
        });
    }

    const destinationTitles = document.querySelectorAll('.destination-group h3');
    destinationTitles.forEach(title => {
        title.addEventListener('click', function() {
            const checkboxes = this.parentNode.querySelectorAll('.jet-form__field-wrap');
            checkboxes.forEach(checkbox => {
                checkbox.classList.toggle('hidden'); // Alternar la clase 'hidden' para ocultar/mostrar los checkboxes
            });
        });
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.classList.add('hidden');
    });


   
});

