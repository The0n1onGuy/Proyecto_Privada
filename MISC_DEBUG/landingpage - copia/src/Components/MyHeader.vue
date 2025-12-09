<template>
<header>
  <div>
    <img src="@/Imagenes/Logo2.png" class="logo-img">
    <span class="logo-text"></span>
  </div>

  <nav>
<<<<<<< HEAD
    <a id="text" class="seccion1" href="#hero"></a>
    <a id="text" class="seccion2" href="#about"></a>
    <a id="text"class="seccion3" href="#features"></a>
    <a id="text" class="seccion4" href="#contacto"></a>
=======
    <a class="seccion1" href="#hero"></a>
    <a class="seccion2" href="#about"></a>
    <a class="seccion3" href="#features"></a>
    <a class="seccion4" href="#contacto"></a>
>>>>>>> ce50aa28daac5e461c64081741daf22c30d50577
  </nav>
</header>
</template>



<<<<<<< HEAD
<style src="@/Estilos/header.css" scoped></style>
=======
<style scoped>
header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  position: relative;
  padding-left: 50px; /* espacio reservado para la imagen */
}

.logo-img {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%); /* centra la imagen verticalmente */
  width: 50px;
  height: 50px;
  object-fit: contain;
}

.logo-text {
  font-size: 22px;
  font-weight: 700;
  color: white;
}


.logo-text::before{
  content: "Nexus Access ";
}
.seccion1::before{
  content: "Inicio";
}
.seccion2::before{
  content: "Acerca";
}
.seccion3::before{
  content: "Funciones";
}
.seccion4::before{
  content: "Contacto";
}

</style>
>>>>>>> ce50aa28daac5e461c64081741daf22c30d50577
