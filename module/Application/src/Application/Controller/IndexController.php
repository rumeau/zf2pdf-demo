<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DOMPDFModule\View\Model\PdfModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function pdfAction()
    {
        $pdf = new PdfModel();
        $pdf->setTerminal(true);
        $pdf->setOption('filename', 'documentoPdf'); // Esta opcion fuerza la descarga del PDF.
        // La extension ".pdf" se agrega automaticamente
        $pdf->setOption('paperSize', 'a4'); // Tamaño del papel

        // Pasamos variables a la vista
        $pdf->setVariables(array(
            'title' => 'Documento PDF',
            'items' => array(
                array('title' => 'Item 1', 'ammount' => 3),
                array('title' => 'Item 2', 'ammount' => 34),
                array('title' => 'Item 3', 'ammount' => 12),
                array('title' => 'Item 4', 'ammount' => 23)
            )
        ));

        // Adjuntar PDF en un correo

        // Obtener una instancia de Zend\Mail\Transport\TransportInterface
        //$mail = $this->getServiceLocator()->get('Zend\Mail\MailService');
        $mail = new \Zend\Mail\Transport\Sendmail();

        // Creamos un nuevo mensaje
        $message = new \Zend\Mail\Message();
        $message->addTo('ejempl@ejemplo.com');
        $message->addFrom('remitente@ejemplo.com', 'JP Rumeau');
        $message->setSubject('PDF Generado con ZF2 y DOMPdf');

        // Agregamos Text Plano y HTML
        $textBody = new \Zend\Mime\Part('Mensaje de ZF2PDF');
        $textBody->type = "text/plain";
        $htmlBody = new \Zend\Mime\Part('<h1>Mensaje de ZF2PDF</h1>');
        $htmlBody->type = "text/html";

        // Solicitamos el HTML generado por el PDF Renderer
        $pdf->setTerminal(true);
        $pdf->setTemplate('application/index/pdf.phtml');
        $htmlPdf = $this->getServiceLocator()->get('viewpdfrenderer')->getHtmlRenderer()->render($pdf);

        $engine = $this->getServiceLocator()->get('viewpdfrenderer')->getEngine();
        // Cargamos el HTML en DOMPDF
        $engine->load_html($htmlPdf);
        $engine->render();
        // Obtenemos el PDF en memoria
        $pdfCode = $engine->output();

        // Creamos un nuevo adjunto, con el PDF
        $attachment = new \Zend\Mime\Part($pdfCode);
        $attachment->type = 'application/pdf';
        $attachment->filename = 'documentoPDF.pdf';
        $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64; // Importante para obtener el adjunto
        $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;

        // Agregamos el PDF al mensaje
        $body = new \Zend\Mime\Message();
        $body->setParts(array($textBody, $htmlBody, $attachment));
        $message->setBody($body);

        // Enviamos el mensaje
        $mail->send($message);

        // Devolver una vista HTML
        return array(
            'title' => 'Documento PDF',
            'items' => array(
                array('title' => 'Item 1', 'ammount' => 3),
                array('title' => 'Item 2', 'ammount' => 34),
                array('title' => 'Item 3', 'ammount' => 12),
                array('title' => 'Item 4', 'ammount' => 23)
            )
        );
    }
}
