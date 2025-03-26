# 🇪🇸➡️🇦🇳 Conjugador Andaluz PHP

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Un script PHP que conjuga verbos del español y los translitera a una propuesta de Estándar Andaluz (EPA) utilizando la librería `andaluh/andaluh-epa` y un servicio externo para la conjugación base en español.

---

## ✨ Características Principales

*   **Conjugación Verbal:** Toma un verbo en infinitivo (en español estándar o con terminaciones andaluzas como `-âh`, `-êh`, `-îh`) y obtiene sus conjugaciones en español.
*   **Transliteración a EPA:** Convierte las conjugaciones obtenidas al estándar EPA propuesto.
*   **Mapeo y Normalización:** Incluye un extenso mapeo (`$verboMappings`) y funciones (`replaceEnding`, etc.) para manejar variantes y normalizar verbos de entrada antes de la conjugación.
*   **API Simple:** Expone la funcionalidad a través de una petición HTTP GET.
*   **Filtrado:** Permite opcionalmente solicitar solo un tiempo (`t`) y modo (`m`) verbal específico.
*   **Correcciones Específicas:** Aplica ajustes post-conjugación para ciertos verbos o casos particulares.

---

## ⚙️ Requisitos Previos

*   **PHP:** Versión 7.4 o superior recomendada.
*   **Extensiones PHP:**
    *   `curl`: Para realizar peticiones HTTP al servicio de conjugación.
    *   `json`: Para manejar las respuestas JSON (generalmente habilitada por defecto).
*   **Composer:** Para gestionar las dependencias PHP.
*   **Docker:** Para ejecutar el servicio de conjugación `verbecc-svc`.
*   **Servicio `verbecc-svc` en ejecución:** Una instancia del servicio [bretttolbert/verbecc-svc](https://github.com/bretttolbert/verbecc-svc) debe estar accesible.

---

## 🚀 Instalación y Configuración

1.  **Clonar el Repositorio:**
    ```bash
    git clone https://github.com/kuasarx/conjugador
    cd <NOMBRE_DEL_DIRECTORIO>
    ```

2.  **Instalar Dependencias PHP:**
    ```bash
    composer install
    ```
    Esto descargará la librería `andaluh/andaluh-epa` y configurará el autoload.

3.  **🐳 Ejecutar el Servicio `verbecc-svc` con Docker:**
    Este script depende de un servicio externo (`verbecc-svc`) para obtener las conjugaciones base en español. Puedes ejecutarlo fácilmente usando Docker:

    ```bash
    docker run -d --name conjugador-servicio -p 32771:8080 bretttolbert/verbecc-svc:latest
    ```
    *   `-d`: Ejecuta el contenedor en segundo plano (detached).
    *   `--name conjugador-servicio`: Asigna un nombre al contenedor para fácil referencia.
    *   `-p 32771:8080`: Mapea el puerto `8080` del contenedor (donde escucha `verbecc-svc`) al puerto `32771` de tu máquina host.

4.  **🚨 ¡Importante! Actualizar la URL del Servicio en el Código:**
    El código PHP tiene hardcodeada la URL `http://192.168.0.84:32771`. Debes cambiarla para que apunte a donde está corriendo tu contenedor Docker.

    *   **Si Docker corre en la misma máquina donde ejecutas PHP:** Probablemente puedas usar `localhost` o `127.0.0.1`.
    *   **Si Docker corre en otra máquina:** Usa la IP de esa máquina.

    Modifica la siguiente línea dentro de la clase `Conjugador`, método `get_conjugaciones`:

    ```php
    // Dentro del método get_conjugaciones()
    // CAMBIA ESTA LÍNEA:
    // $url = "http://192.168.0.84:32771/conjugate/es/".urlencode($this->verbo);
    // POR ESTA (si Docker está en la misma máquina):
    $url = "http://localhost:32771/conjugate/es/".urlencode($this->verbo);
    // O usa la IP correcta si Docker está en otra máquina.
    ```

---

## 💡 Uso

Para usar el conjugador, necesitas ejecutar el script PHP a través de un servidor web (como Apache o Nginx con PHP-FPM) o usando el servidor web incorporado de PHP para pruebas:

```bash
php -S localhost:8000
```

Luego, realiza una petición GET al script, pasando el verbo a conjugar en el parámetro `q`.

**Ejemplos:**

*   **Conjugar "hablâh":**
    ```
    http://localhost:8000/conhugaôh.php?q=hablâh
    ```
    (Asumiendo que tu archivo se llama `conhugaôh.php`)

*   **Conjugar "comêh":**
    ```
    http://localhost:8000/conhugaôh.php?q=comêh
    ```

*   **Conjugar "bîbbîh":**
    ```
    http://localhost:8000/conhugaôh.php?q=bîbbîh
    ```

*   **Conjugar "çêh" (verbo 'ser'):**
    ```
    http://localhost:8000/conhugaôh.php?q=çêh
    ```

*   **Obtener solo el presente de indicativo de "açêh" (verbo 'hacer'):**
    *(Nota: Los parámetros `m` (modo) y `t` (tiempo) deben usar las claves transliteradas devueltas por la API)*
    ```
    http://localhost:8000/conhugaôh.php?q=açêh&m=indicatibo&t=preçente
    ```

*   **Obtener solo el infinitivo de "paçâh" (verbo 'pasar'):**
    ```
    http://localhost:8000/conhugaôh.php?q=paçâh&m=infinitibo
    ```

**Respuesta:**
El script devolverá una estructura JSON con las conjugaciones transliteradas al estándar EPA. Si se especifican `m` y `t`, la respuesta se filtrará a ese modo y tiempo específicos.

---

## 📄 Estructura del Código

*   **`Conjugador` (Clase Principal):**
    *   `__construct($verbo)`: Inicializa el objeto, normaliza el verbo de entrada y obtiene las conjugaciones base.
    *   `partialTranscribtion($verbo)`: Convierte terminaciones como `-âh` a `-ar`.
    *   `get_conjugaciones()`: Realiza la llamada cURL al servicio `verbecc-svc` para obtener las conjugaciones en español.
    *   `translate_array($arr)`: Helper para transliterar los elementos de un array usando `AndaluhEpa`.
    *   `conjugate()`: Orquesta la transliteración de las conjugaciones españolas al formato EPA, manejando pronombres y estructuras específicas.
*   **`$verboMappings` (Array):** Un diccionario para mapear formas verbales de entrada específicas a su infinitivo estándar o forma base antes de la conjugación.
*   **Funciones de Reemplazo (`replaceEnding`, `replaceFirst_H_Letter`, etc.):** Aplican reglas de sustitución de cadenas para normalizar o corregir la forma del verbo antes o después de la conjugación.
*   **Lógica Principal (Fuera de la clase):**
    *   Procesa el parámetro `$_REQUEST['q']`.
    *   Aplica mapeos y reemplazos iniciales.
    *   Instancia `Conjugador`.
    *   Obtiene las conjugaciones (`conjugate()`).
    *   Aplica correcciones post-conjugación.
    *   Devuelve el resultado en formato JSON, aplicando filtros si `$_REQUEST['t']` y `$_REQUEST['m']` están presentes.

---

## 🔗 Dependencias

*   **[andaluh/andaluh-epa](https://github.com/andaluh/andaluh-epa):** Librería PHP para la transliteración al estándar EPA.
*   **[bretttolbert/verbecc-svc](https://github.com/bretttolbert/verbecc-svc):** Servicio web (ejecutable con Docker) que proporciona las conjugaciones verbales base en español.

---

## Licencia

Este proyecto se distribuye bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles (si existe) o consultar [MIT License](https://opensource.org/licenses/MIT).
