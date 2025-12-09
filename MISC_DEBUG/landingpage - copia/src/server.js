const express = require("express");
const cors = require("cors");
const nodemailer = require("nodemailer");

const app = express();
app.use(cors());
app.use(express.json());

app.post("/enviar", async (req, res) => {
  const { nombre, correo, mensaje } = req.body;

  const transporter = nodemailer.createTransport({
    service: "gmail",
    auth: {
      user: "TUCORREO@gmail.com",
      pass: "TU_CONTRASEÑA_DE_APLICACIÓN"
    }
  });

  await transporter.sendMail({
    from: "TUCORREO@gmail.com",
    to: "DESTINATARIO@gmail.com",
    subject: "Nuevo mensaje desde el formulario",
    html: `
      <h3>Contacto desde la web</h3>
      <p><b>Nombre:</b> ${nombre}</p>
      <p><b>Correo:</b> ${correo}</p>
      <p><b>Mensaje:</b><br>${mensaje}</p>
    `
  });

  res.send({ message: "Correo enviado" });
});

app.listen(3000, () => console.log("Servidor en puerto 3000"));
