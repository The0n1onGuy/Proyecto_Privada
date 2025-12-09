<template>
<section id="contacto">
  <h2 class="titule"></h2>
  <p class="desc"></p>

  <form class="contact-form">
      <label>Nombre:</label>
      <input type="text" placeholder="Tu nombre completo" required>

      <label>Correo:</label>
      <input type="email" placeholder="correo@ejemplo.com" required>

      <label>Mensaje:</label>
      <textarea placeholder="Escribe tu mensaje o duda" required></textarea>

<<<<<<< HEAD
      <button id="text" type="submit">Enviar Solicitud</button>
=======
      <button type="submit">Enviar Solicitud</button>
>>>>>>> ce50aa28daac5e461c64081741daf22c30d50577
  </form>
</section>
</template>

<<<<<<< HEAD
<style src="@/Estilos/contacto.css" scoped></style>
=======
<style scoped>
.titule::before{
    content: "Contactanos...";
}
.desc::before{
    content: "Solicita acceso a una demostración personalizada o recibe información para implementar Nexus Access en tu privada.";
}

#contacto {
    padding: 80px 50px;
    text-align: center;
    animation: fadeIn 1.2s ease;
}

#contacto h2 {
    font-size: 45px;
    margin-bottom: 15px;
    color: #1f2b6c;
}

#contacto .desc {
    max-width: 750px;
    margin: auto;
    font-size: 22px;
    line-height: 1.6;
    color: #333;
    margin-bottom: 40px;
}

.contact-form {
    background: white;
    max-width: 650px;
    margin: auto;
    padding: 28px 35px;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.07);
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
    display: flex;
    flex-direction: column;
    gap: 18px;
    text-align: left;
    animation: fadeInUp 1.1s ease;
}

.contact-form label {
    font-size: 18px;
    font-weight: 600;
    color: #1f2b6c;
    margin-bottom: -8px;
}

.contact-form input,
.contact-form textarea {
    padding: 14px;
    font-size: 17px;
    border-radius: 10px;
    border: 1px solid #d6d6d6;
    background: #f5f7ff;
    transition: border .25s ease, box-shadow .25s ease, background .25s ease;
    font-family: 'Segoe UI', sans-serif;
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #1f2b6c;
    background: #ffffff;
    box-shadow: 0 0 10px rgba(31,43,108,0.25);
}

.contact-form textarea {
    resize: vertical;
    min-height: 135px;
}

.contact-form button {
    margin-top: 10px;
    padding: 14px;
    font-size: 18px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: #1f2b6c;
    color: white;
    transition: transform .3s ease, background .3s ease, box-shadow .3s ease;
}

.contact-form button:hover {
    background: #293688;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(31,43,108,0.25);
}

</style>
>>>>>>> ce50aa28daac5e461c64081741daf22c30d50577
