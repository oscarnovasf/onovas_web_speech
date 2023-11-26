onovas: Web Speech
===

>Nombre de máquina: onovas_web_speech

[![version][version-badge]][changelog]
[![Licencia][license-badge]][license]
[![Código de conducta][conduct-badge]][conduct]
[![wakatime](https://wakatime.com/badge/user/236d57da-61e8-46f2-980b-7af630b18f42/project/018bd461-6fb9-4886-b661-eeef0faf1ee5.svg)](https://wakatime.com/badge/user/236d57da-61e8-46f2-980b-7af630b18f42/project/018bd461-6fb9-4886-b661-eeef0faf1ee5)

---

## Información
Módulo que permite añadir un pseudo campo a los contenidos para ofrecer la
posibilidad de leer el contenido del nodo a través del API para Web Speech.  
También ofrece un bloque configurable para con la misma funcionalidad.

---

## Requisitos
Este módulo necesita para su correcto funcionamiento una versión superior
a la 10.x de Drupal.

---

## Instalación
Este módulo se instala como cualquier otro módulo de Drupal.  
No es necesario un proceso de instalación más avanzado.

Se recomienda, eso sí, instalarlo en la ruta **modules/custom/** para que se
instale la traducción al castellano.

---

## Configuración
El el formulario de configuración proporcionado por el módulo seleccionamos la
voz por defecto, la velocidad y el tono.  
También seleccionamos el contenedor por defecto cuyo contenido será leído (este
contendor se podrá cambiar si se usa el bloque en lugar del pseudo campo).

---
[version]: v1.0.0
[version-badge]: https://img.shields.io/badge/Versión-1.0.0-blue.svg

[license]: LICENSE.md
[license-badge]: https://img.shields.io/badge/Licencia-GPLv3+-green.svg "Leer la licencia"

[conduct]: CODE_OF_CONDUCT.md
[conduct-badge]: https://img.shields.io/badge/C%C3%B3digo%20de%20Conducta-2.0-4baaaa.svg "Código de conducta"

[changelog]: CHANGELOG.md "Histórico de cambios"
