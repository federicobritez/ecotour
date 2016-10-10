<?php
/*
 * DefaultController.php
 *
 * Copyright 2016 Federico Britez <fedebritez@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;



use AppBundle\Entity\Cliente;
use AppBundle\Entity\Reserva;
use AppBundle\Entity\EstadoReserva;
use AppBundle\Entity\HorariosReserva;
use AppBundle\Entity\ReservasHabitaciones;
use AppBundle\Entity\Habitaciones;
use AppBundle\Entity\TipoPago;
use AppBundle\Entity\ConsumosCliente;

class DefaultController extends Controller
{
 /**
  * Render EcoTourApp page.
  *
  * @Route("/index", name="login_page" , defaults={"page"="listado"})
  *
  * @param Request $request
  *
  *
  * @return Response
  */
  public function indexAction(Request $request, $page="login")
  {

  	if($page == "login"){
  		if($this->get('session') == null){
  			$session = new Session();
  			$session->start();
  		}
  	}
  	elseif($page == "logout"){

  		$session = $this->get('session');
  		if($session != null){
  			$session->invalidate();
  		}
  	}

	return $this->render(sprintf('ecotour/%s.html.twig', "login"));
  }
	
	
	
/**
  * Render EcoTourApp page.
  *
  * @Route("/verificarLogin", name="verif_login_page" )
  *
  * @param Request $request
  *
  *
  * @return Response
  */
  public function verifacarLogin(Request $request){

  	$em = $this->getDoctrine()->getManager();
	$nombre = $request->get('usuario');
	$pass = $request->get('psw');

	$user = $em->getRepository('AppBundle:Usuario')->findOneBy(array('usuario'=>$nombre));

	if($user != null ){

		if($user->getClave() == $pass){

			$this->get('session')->set('usuario_nombre', $user->getUsuario());
			$this->get('session')->set('usuario_template', $user->getPerfil()->getVista()->getNombrePlantilla());

			$cliente = $user->getCliente();
			if($cliente != null){
				$this->get('session')->set('cliente_nombre', $cliente->getNombre());
			}

			return $this->redirectToRoute('informes_page');
		}		
	}
	
	return $this->render(sprintf('ecotour/%s.html.twig', "login"));	
  }
  
  
  
 /**
  * Render Home page.
  *
  * @Route("/home", name="home_page")
  *
  * @param Request $request
  *
  *
  * @return Response
  */
  public function homeAction(Request $request)
  {

  	return $this->redirectToRoute('clientesAtendidos');

  }



 /**
  * Render Reservas page.
  *
  * @Route("/reservas/{page}", name="reservas_page", defaults={"page"="listado"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */
 public function reservasAction(Request $request, $page= 'listado')
 {
 	
	$em = $this->getDoctrine()->getManager();

	if($page == "listado"){

		/*
			Consulta de Todas las Reservas
		*/

  		$reservas = $em->createQuery('SELECT r FROM AppBundle:Reserva r ORDER BY r.fechaReserva DESC')->getResult();

  		$estado_reserva = $em->createQuery('SELECT er FROM AppBundle:EstadoReserva er')->getResult();


		return $this->render(sprintf('ecotour/%s.html.twig', "reservasListado"),
							array('reservas' => $reservas,
								  'estadoReserva' => $estado_reserva ));

  	}
  	else if($page == "nueva"){

		/*	 Consulta de Todos los Servicios Activos				*/

  		$query = $em->createQuery('SELECT sr FROM AppBundle:ServiciosReservables sr');
		$servReservables = $query->getResult();
		

		/*  Listado de Clientes 			   */
		$query = $em->createQuery('SELECT c FROM AppBundle:Cliente c ORDER BY c.apellido ASC');
		$clientes = $query->getResult(); // array de Objetos Cliente
		

		$txtcliente= "";
		foreach($clientes as $c){
			$txtcliente .= $c->getId().':'.'"'. implode(" ",array($c->getApellido(), $c->getNombre(),"|",$c->getId())).'",';
		}
		rtrim($txtcliente,',');
		
		

		$clientes = 'var clientes = {'.$txtcliente.'};';

		/*		Habitaciones Disponibles para la Posada */

		$query = $em->createQuery('SELECT h FROM AppBundle:Habitaciones h WHERE h.estadoHabitacion = 0 ORDER BY h.valorHabitacion DESC' );
		$habitaciones = $query->getResult(); // array de Objetos Habitacion
		
		
		/*		Plazas vacantes por servicio  */		

		/* Horarios*/

		$horariosServ = array();
		foreach($servReservables as $item){
			$id = $item->getServicio()->getId();
			$horarios = $conn->fetchAll('SELECT * FROM ecoturismo.horarios_servicio WHERE servicio_id ='.$id.' ORDER BY hora_inicio, hora_fin asc ;' );

			if(count($horarios)>0){
				$horariosServ["$id"] = $horarios;
			}
		}

		return $this->render(sprintf('ecotour/%s.html.twig', "reservasWizard"),
									array('servicios' => $servReservables, 
											'clientes' => $clientes, 
											'habitaciones' => $habitaciones, 
											'horarios'=>$horariosServ));
		
	}
	else if( $page == "finalizar"){

		$cliente = null;  // El cliente que hace la reserva
		
		if($request->request->get('origenCliente') == "registrado"){
			
			$arrCliente = explode("|",$request->get('cliente_registrado')); 		// cliente : ApallidoNombre|ID

			if(!is_numeric($arrCliente[1])){ }	//TODO PANTALLA DE ERROR

			$idCliente = $arrCliente[1];
			
			$cliente = $this->getDoctrine()->getRepository('AppBundle:Cliente')->find($idCliente);

			if(!$cliente){	} //TODO PANTALLA DE ERROR

		}
		else if ($request->get('origenCliente') == "nuevo"){
				
				$cliente  = new Cliente();
				$cliente->setNombre($request->request->get('nombre'));
				$cliente->setApellido($request->request->get('apellido'));
				$cliente->setTelefono1($request->request->get('telefono1'));
				$cliente->setTelefono2($request->request->get('telefono2'));
				$cliente->setCiudad($request->request->get('ciudad'));
				$cliente->setProvincia($request->request->get('provincia'));
				$cliente->setPais($request->request->get('pais'));
				$cliente->setDni($request->request->get('dni'));

				$em->persist($cliente);
				$em->flush();
		}

		/*Servicios con horarios*/

		$horarios = $conn->fetchAll('SELECT * FROM ecoturismo.horarios_servicio hs GROUP BY hs.servicio_id');
		$horariosServ = array();
		if(count($horarios)>0){
			
			$horariosServ = array_column($horarios,'servicio_id');
			
		}

		/*	Servicios Activos */
		$totalValorReservas = 0;

		foreach($request->request->get('servicios') as $idServ){
	
			$servicio = $this->getDoctrine()->getRepository('AppBundle:Servicio')->find($idServ);

			$arrFechas = explode("-",$request->get('reservation'.$idServ));
			$fechaInicio = $arrFechas[0];
			$fechaFin = $arrFechas[1];
			$cant = $request->get('serv_'.$idServ.'_cant');
			
			
			/* 	 */
			$reserva = new Reserva();
			$reserva->setFechaReserva(new \DateTime(date("Y-m-d H:i:s")));
			$reserva->setFechaDesde(new \DateTime($fechaInicio));
			$reserva->setFechaHasta(new \DateTime($fechaFin));
			$reserva->setCliente($cliente);
			$reserva->setCantPersonas($cant);
			$reserva->setServicio($servicio);

			$valorReserva = $this->calcularValorReserva($reserva);
			$reserva->setValorTotal($valorReserva);

			$totalValorReservas += $valorReserva;
			

			/* Seteamos el estado por default que es Esperando Confirmacion*/
			$estadoReserva = $this->getDoctrine()->getRepository('AppBundle:EstadoReserva')->find(3);
			$reserva->setEstadoReserva($estadoReserva);
	
			$em->persist($reserva);
			$em->flush();				
			
			/*  */
			$horario = null;
			if(in_array($idServ,$horariosServ)){
				$idHorario = $request->request->get('serv_'.$idServ.'_horario');
				$horario  = $this->getDoctrine()->getRepository('AppBundle:HorariosServicio')->find($idHorario);

			}

			if($horario != null){
				$horarios_reserva = new HorariosReserva();
				$horarios_reserva->setReserva($reserva);
				$horarios_reserva->setHorariosServicio($horario);
				
				$em->persist($horarios_reserva);
				$em->flush();		
			}

			/* Habitaciones de la posada*/
			if($idServ == 2){
				foreach($request->request->get('habitaciones') as $idHabit){
					
					$habitacion = $this->getDoctrine()->getRepository('AppBundle:Habitaciones')->find($idHabit);
					
					$reservasHabitaciones= new ReservasHabitaciones();
					$reservasHabitaciones->setReserva($reserva);
					$reservasHabitaciones->setHabitaciones($habitacion);
					
					$em->persist($reservasHabitaciones);
					$em->flush();
				}
			}
		}

		return $this->render(sprintf('ecotour/%s.html.twig', "reservasFinalizar"),
									array('totalValorReservas' => $totalValorReservas));
	} //endif finalizar
	else if ($page  == "editar"){
		$idReserva = $reques->get('idReserva');
		$reserva = $em->getRepository('AppBundle:Reserva')->find($idReserva);
		$habitaciones = $em->getRepository('AppBundle:ReservasHabitaciones')->findBy(array('reserva'=>$reserva->getId()));

		/*
			foreach($habitaciones as $habitacion){
				//Si la reserva pasa a Estado  EN_TRANSITO Se ocupan las habitaciones
				if($idEstadoReserva == 7) { 		$habitacion->setEstadoHabitacion(1); }
				//Si la reserva pasa a SALIDA se desocupan las habitaciones
				else if($idEstadoReserva == 6){  $habitacion->setEstadoHabitacion(0); }
			}
		*
		*/
		$reserva->setEstadoReserva($em->getRepository('AppBundle:EstadoReserva')->find($idEstadoReserva));
		$em->flush();
	}
 }


 public function calcularValorReserva($reserva){

 	/*El valor se calcula:
		Total = ((V.unitarioServicio)*CantPersonasReserva)*Cantidad de Dias  
 	*/
	if( $reserva == null){
		return -1;
	}

	//Cantidad de dias :
	$intervalo = $reserva->getFechaDesde()->diff($reserva->getFechaHasta());

	$dias = intval($intervalo->format('%R%a'))+1; //Sumamos un dia mas porque 

	$precioUnitario = $reserva->getServicio()->getValorUnitario();
	$cantPersonas = $reserva->getCantPersonas();

	return $precioUnitario*$dias*$cantPersonas;

 }


 /**
  * Render Informes page
  *
  * @Route("/informes/{page}", name="informes_page", defaults={"page" = "clientesAtendidos"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */

  public function informesAction(Request $request, $page = 'clientesAtendidos')
  {
		$em = $this->getDoctrine()->getManager();

		if($page == "clientesAtendidos"){

			$query = $em->createQuery('SELECT sr FROM AppBundle:ServiciosReservables sr');
			$serv_reservables = $query->getResult();

				
			/*  Listado de Consumos del día 			   */
	
			//$query = $em->createQuery('SELECT co FROM AppBundle:ConsumosCliente co WHERE co.fecha ='.date("Y-m-d").' AND co.servicio = :servicio  ORDER BY co.fecha  ASC');

			$query = $em->createQuery('SELECT co FROM AppBundle:ConsumosCliente co WHERE  co.servicio = :servicio  ORDER BY co.fecha  ASC');

			$consumos = array();  // Arreglo diccionario donde guardar los consumos de cada servicio.
			
			$i = 0;
			foreach($serv_reservables as $sr){
				$query->setParameter('servicio', $sr->getServicio()->getId());
				
				$servNombre = $sr->getServicio()->getNombre();
				
				$resQuery = $query->getResult();

				if(count($resQuery) > 0){
					$consumos[$servNombre] = $resQuery; // array de Objetos ConsumosCliente
				}
				$i++;
			}
			$qb = $em->createQueryBuilder();
			$qb->select('SUM(co.montoAbonado) as sumaTotal')->from('AppBundle:ConsumosCliente','co');
			$qb->where('co.fecha BETWEEN :hoy AND :ahora')->setParameter('hoy',date("Y-m-d 00:00"))->setParameter('ahora',date("Y-m-d h:m"));

			$query = $qb->getQuery();
			$montoTotal = $query->getSingleResult();
			//$montoquery = $conn->fetchAll('SELECT SUM(co.monto_abonado) as sumaTotal FROM consumos_cliente co WHERE co.fecha  BETWEEN \''.date("Y-m-d 00:00").'\' AND \'' .date("Y-m-d ").'\';');

			//$montoTotal = $montoquery[0]['sumaTotal'];
	
			return $this->render(sprintf('ecotour/%s.html.twig',"informesClientesAtendidos"),
								array('consumos'=>$consumos,
									  'montoTotal'=>$montoTotal));

		}
		else if($page == "reservasProgramadas"){

			$qb = $em->createQueryBuilder();
			$tomorrow = new \DateTime('tomorrow');
		
			$tomorrow= $tomorrow->format("Y-m-d");
			
			$qb->select('re')->from('AppBundle:Reserva','re')->where('re.fechaDesde BETWEEN :hoy AND :maniana')->andWhere('re.estadoReserva = :estado')->setParameter('hoy',date("Y-m-d"))->setParameter('maniana',$tomorrow)->setParameter('estado',3);



			//$queryReserv = $em->createQuery('SELECT re FROM AppBundle:Reserva re WHERE re.fechaDesde =\''.date("Y-m-d").'\' AND re.estadoReserva = :estado ORDER BY re.fechaDesde ASC');


			$estadosValidos = array(1,3); //CONFIRAMADA y ESPERANDO_CONFIRMACION

			$reservasProgramadas = $qb->getQuery()->getResult();

			//$estadosRepo = $em->getRepository('AppBundle:EstadoReserva');
			/*
			foreach($estadosValidos as $idEstado){

				$estadoReserva = $estadosRepo->find($idEstado);

				$queryReserv->setParameter('estado',$estadoReserva->getId());

				$reservasProgramadas[$estadoReserva->getDescripcion()] =  $queryReserv->getResult();
			}
			*/

			return $this->render(sprintf('ecotour/%s.html.twig',"informesReservasProgramadas"),
										array('reservasProg' => $reservasProgramadas));

		}
		elseif($page == "listaEspera"){

			$estadosRepo = $em->getRepository('AppBundle:EstadoReserva');
			$estadoReserva = $estadosRepo->find(2); //LISTA_ESPERA
			
			$queryReserv = $em->createQuery('SELECT re FROM AppBundle:Reserva re WHERE re.estadoReserva = :estado ORDER BY re.fechaDesde ASC');
			$queryReserv->setParameter('estado',$estadoReserva->getId());
			$reservasEspera =  $queryReserv->getResult();

			return $this->render(sprintf('ecotour/%s.html.twig',"informesListaEspera"),array('reservas' => $reservasEspera));
		}
		
		

  }


 /**
  * Render Clientes page
  *
  * @Route("/clientes/{page}", name="clientes_page", defaults={"page" = "listado"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */


 public function clientesAction(Request $request, $page = 'listado') {
 	
 	$em = $this->getDoctrine()->getManager();
 	
 	if($page == "listado"){

	/*  Listado de Clientes 			   */
		$query = $em->createQuery('SELECT c FROM AppBundle:Cliente c ORDER BY c.apellido ASC');
		$clientes = $query->getResult(); // array de Objetos Cliente

	/* Las Reservas */
		$reservas = $em->getRepository('AppBundle:Reserva');

	/*  Listado de Servicios No Reservables	   */
		$query = $em->createQuery('SELECT s FROM AppBundle:Servicio s WHERE s.id NOT IN (SELECT sr FROM AppBundle:ServiciosReservables sr ) ORDER BY s.nombre ASC');
		$servicios = $query->getResult(); // array de Objetos Servicio


	/* Formas de Pago de Cada Servicio*/
		$formasPago = $em->getRepository('AppBundle:FormasDePago');

		return $this->render(sprintf('ecotour/%s.html.twig',"clientesListado"),
									array('clientes' => $clientes ,
										  'reservas' => $reservas));

	}
	else if($page== "porFecha"){
		return $this->render(sprintf('ecotour/%s',"clientesListado2.html.twig"),
									array('clientes' => $clientes,
										  'reservas'=>$reservas));

	}
	else if($page == "editar"){

		$idCliente = $request->get('id');
		$cliente =  $em->getRepository('AppBundle:Cliente')->find($idCliente);

		return $this->render(sprintf('ecotour/%s',"clientesEditar.html.twig"),
									array('cliente' => $cliente));

	}
	else if($page == "nuevo"){

		$cliente = new Cliente();
		return $this->render(sprintf('ecotour/%s',"clientesEditar.html.twig"),
									array('cliente' => $cliente));

	}

 }

 /**
  * Render Pagos page
  *
  * @Route("/pagos/{page}", name="pagos_page", defaults={"page" = "ingresar"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */
  
 public function pagosAction(Request $request, $page = 'ingresar'){
 	$em = $this->getDoctrine()->getManager();
 	if($page == "ingresar"){
 		
		$idCliente = $request->query->get('id');
		$cliente =  $em->getRepository('AppBundle:Cliente')->find($idCliente);

		/*  Listado de Servicios No Reservables	   */
		$query = $em->createQuery('SELECT tp FROM AppBundle:TipoPago tp');
		$tipo_pago = $query->getResult(); 
		/*
		SELECT rr.*
FROM ecoturismo.reserva rr
LEFT JOIN ecoturismo.consumos_cliente sm ON rr.id = sm.reserva_id
WHERE sm.reserva_id IS NULL ;
	*/

		// Reservas no abonados por el cliente- No esta en ConsumoCliente
		$qb = $em->createQueryBuilder();
		$qb->select('rr')->from('AppBundle:Reserva', 'rr')->leftJoin('AppBundle:ConsumosCliente', 'cc','WITH','rr.id = cc.reserva')->andWhere("cc.reserva IS NULL")->andWhere("rr.cliente=:idcliente")->setParameter("idcliente",$cliente->getId());

		$reservasCliente = $qb->getQuery()->getResult();

		/*
		$reservasCliente = $this->getDoctrine()->
											getRepository('AppBundle:Reserva')->
													findBy(array('cliente'=> $cliente));
		*/

		return $this->render(sprintf('ecotour/%s.html.twig',"pagosIngresar"),
									array('request' => $query,
											'reservas'=> $reservasCliente,
											'cliente' => $cliente,
											'tipo_pago' => $tipo_pago));
		
	}
	elseif($page == "comprar"){
		
		$idCliente = $request->query->get('id');
		$cliente   = $em->getRepository('AppBundle:Cliente')->find($idCliente);
		$mercaderia= $em->getRepository('AppBundle:Mercaderia')->
									findBy(array(), array('nombre' => 'ASC'));

		return $this->render(sprintf('ecotour/%s.html.twig',"comprar"),
									array('request' => $request,
											'cliente' => $cliente,
											'mercaderia'=> $mercaderia));
	}
	elseif($page == "ver_factura"){
		$idReserva = $request->get("id_reserva");
		$idTipoPago = $request->get("id_tipopago");

			
		/*Obtengo la reserva*/
		$reserva = $this->getDoctrine()->getRepository('AppBundle:Reserva')->find($idReserva);

		if($reserva == null){
			$respuesta= "ERROR";
		}
		else{
			$cliente = $reserva->getCliente();
			$tipoPago = $em->getRepository('AppBundle:TipoPago')->find($idTipoPago);

			$saldo =  array();
			$saldo["subtotal"] = $this->calcularValorReserva($reserva);

			$interes = ($tipoPago->getInteres()/100)* $saldo["subtotal"];
				
			$saldo["interes"] = $interes;

			$saldo["iva"] = 0.21 * $saldo["subtotal"];

			$saldo["total"] = $saldo["subtotal"] + $saldo["interes"]  + $saldo["iva"];
			
			return $this->render(sprintf('ecotour/%s.html.twig',"pagosVerFactura"),
								array('reserva' => $reserva, 
									  'cliente' => $cliente,
									  'tipoPago'=> $tipoPago,
									  'detalleSaldo' => $saldo,
									  'serverTime' => new \DateTime(date("Y-m-d H:i:s"))));
		}
	}
  }


 /**
  * Render Servicios page
  *
  * @Route("/servicios/{page}", name="servicios_page", defaults={"page" = "listado"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */

 public function serviciosAction(Request $request, $page = 'listado'){
 	//Listar servicios y clasificarlos por tipos...
	$em = $this->getDoctrine()->getManager();

	if($page== "listado"){
		/*  Listado de Servicios No Reservables	   */
		$query = $em->createQuery('SELECT s FROM AppBundle:Servicio s WHERE s.id NOT IN (SELECT sr FROM AppBundle:ServiciosReservables sr ) ORDER BY s.nombre ASC');
		$servicios = $query->getResult(); // array de Objetos Servicio

		/* Servicios Reservables */
		$query = $em->createQuery('SELECT sr FROM AppBundle:ServiciosReservables sr');
		$servReservables = $query->getResult();


		$total_recaudado=10000;
		$total_credito=4000;
		$total_debito=2000;
		$total_efectivo=4000;

		$totales = array("recaudado"=> $total_recaudado, "credito"=>$total_credito, "efectivo"=>$total_efectivo,"debito"=>$total_debito);

		return $this->render(sprintf('ecotour/%s.html.twig',"serviciosListado"),
									array('servicios' => $servicios,
										  'serviciosReservables'=>$servReservables,
										  'totales'=>$totales));

	}
	elseif($page == "porFecha"){
		
		if( ($request->get('fechaInicio') !== null)  || ($request->get('fechaFin') !== null) ){
			$fechaInicio = date(new \DateTime(date("Y-m-d H:i:s")));
			$fechaFin = $fechaInicio;
		}else{
			$fechaInicio = date(new \DateTime($request->get('fechaInicio')));
			$fechaFin = date(new \DateTime($request->get('fechaFin')));
		}
		
	/* Total de servicios realizados en un perídos de tiempo determinado*/
		$query = $em->createQuery('SELECT c FROM AppBundle:ConsumoCliente c WHERE c.fecha >= :fechaInicio AND fecha <= :fechaFin  ORDER BY c.fecha ASC'); // array de Objetos Servicio
		$query->setParameter('fechaInicio', $fechaInicio);
		$query->setParameter('fechaFin', $fechaFin);
		$consumosCliente = $query->getResult();
		
		return $this->render(sprintf('ecotour/%s.html.twig',"serviciosPorFecha"),array('clientes' => $clientes));

	}
	elseif($page == "configuracion"){

		return $this->render(sprintf('ecotour/%s.html.twig',"serviciosConfiguracion"));		

	}
 }
 


 /**
  * Render Ayuda page
  *
  * @Route("/Ayuda/{page}", name="ayuda_page", defaults={"page" = "index"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */

 public function ayudaAction(Request $request, $page = 'index'){

 	return $this->render(sprintf('ecotour/%s.html.twig',"ayudaIndex"),array('var' => "1"));

 }
 
 /**
  * Render Debug page
  *
  * @Route("/debug/{page}", name="debug", defaults={"page" = "debug"})
  *
  * @param Request $request
  * @param string  $page    Page name
  *
  * @return Response
  */

 public function debugAction(Request $request, $page = 'debug'){
		
		return $this->render(sprintf('ecotour/%s.html.twig',"testValidator"));
		
 }


/**
 -------------------------------------------------------------------------------------------
 						AJAX RESPONSE
*/

/**
  * Ajax Servicios page
  *
  * @Route("/ajaxServicios/servSelect", name="ajax_serv_select")
  *
  * @param Request $request
  *
  * @return Response
  */

  public function ajaxServiciosAction(Request $request){

		//$servicios = $request->get("input");
		$servicios = "HOLLLLAAA";

		//return $this->render(sprintf('ecotour/%s.html.twig', "reservasWizard"),array("test"=>$request));
	  //you can return result as JSON


	   return new JsonResponse(array('data' => $servicios));
  }


  /**
  * Ajax Reservas
  *
  * @Route("/ajaxServicios/estadoReserva", name="ajax_cambio_estado_reserva")
  *
  * @param Request $request
  *
  * @return Response
  */

  public function ajaxEstadoReservaAction(Request $request){

  		$em = $this->getDoctrine()->getManager();			

		$id_estado = $request->get("id_estado");
		$id_reserva = $request->get("id_reserva");
		$respuesta="";		
		/*Obtengo la reserva*/
		$reserva = $this->getDoctrine()->getRepository('AppBundle:Reserva')->find($id_reserva);


		if($reserva == null){
			$respuesta= "ERROR";
		}
		else{

			$estadoReserva = $this->getDoctrine()->getRepository('AppBundle:EstadoReserva')->find($id_estado);
			$reserva->setEstadoReserva($estadoReserva);
			$em->persist($reserva);	$em->flush();
			$respuesta= "OK";
		}


	   return new JsonResponse(array('mje' => $respuesta));
  }


  /**
  * Ajax Reservas
  *
  * @Route("/ajaxServicios/eliminarReserva", name="ajax_eliminar_reserva")
  *
  * @param Request $request
  *
  * @return Response
  */

  public function ajaxEliminarReservaAction(Request $request){

  		$em = $this->getDoctrine()->getManager();			
		$id_reserva = $request->get("id_reserva");
		$respuesta="";		
		/*Obtengo la reserva*/
		$reserva = $this->getDoctrine()->getRepository('AppBundle:Reserva')->find($id_reserva);

		if($reserva == null){
			$respuesta= "ERROR";
		}
		else{

			$em->remove($reserva); $em->flush();
			$respuesta= "OK";
		}

	   return new JsonResponse(array('mje' => $respuesta));
  }

  

  /**
  * Ajax Reservas
  *
  * @Route("/ajaxServicios/editarCliente", name="ajax_cliente_editar")
  *
  * @param Request $request
  *
  * @return Response
  */

  public function ajaxEditarClienteAction(Request $request){

  		$em = $this->getDoctrine()->getManager();			

  		$idCliente = $request->get("id_cliente");

		$nombre = $request->get("nombre");
		$apellido = $request->get("apellido");
		$dni = $request->get("dni");
		$telefono1 = $request->get("telefono1");
		$telefono2 = $request->get("telefono2");
		$correo = $request->get("correo");
		$pais = $request->get("pais");
		$provincia = $request->get("provincia");
		$localidad = $request->get("localidad");
		
		$respuesta="";

		if($idCliente == null ){
			$cliente = new Cliente();
		}
		else{
			$cliente = $this->getDoctrine()->getRepository('AppBundle:Cliente')->find($idCliente);	
		}

		
		
		if($cliente == null){
			$respuesta= "ERROR";
		}
		else{

			$cliente->setNombre($nombre);
			$cliente->setApellido($apellido);
			$cliente->setDni($dni);
			$cliente->setTelefono1($telefono1);
			$cliente->setTelefono2($telefono2);
			$cliente->setCorreo($correo);
			$cliente->setPais($pais);
			$cliente->setProvincia($provincia);
			$cliente->setCiudad($localidad);

			$em->persist($cliente); 
			$em->flush();
			$respuesta= "OK";
		}

	   return new JsonResponse(array('mje' => $respuesta));
  }



  
  /**
  * Ajax Pagos
  *
  * @Route("/ajaxServicios/pagorReserva", name="ajax_pagar_reserva")
  *
  * @param Request $request
  *
  * @return Response
  */

  public function ajaxPagarReservaAction(Request $request){

  		$em = $this->getDoctrine()->getManager();			
		$id_reserva = $request->get("id_reserva");
		$id_tipopago = $request->get("id_tipopago");
		$montoTotal = $request->get("monto_abonado");
		$monto_abonado = $request->get("monto_abonado");

		$respuesta="";	

		/*Obtengo la reserva*/
		$reserva = $this->getDoctrine()->getRepository('AppBundle:Reserva')->find($id_reserva);

		if($reserva == null){
			$respuesta= "ERROR";
		}
		else{
			$tipoPago = $this->getDoctrine()->getRepository('AppBundle:TipoPago')->find($id_tipopago);

			$consumosCliente = new ConsumosCliente();
			$consumosCliente->setCliente($reserva->getCliente());
			$consumosCliente->setServicio($reserva->getServicio());
			$consumosCliente->setMontoAbonado($monto_abonado);
			$consumosCliente->setSaldo($montoTotal-$monto_abonado);
			$consumosCliente->setTipoPago($tipoPago);
			$consumosCliente->setFecha(new \DateTime(date("Y-m-d H:i:s")));
			$consumosCliente->setReserva($reserva);

			$em->persist($consumosCliente);

			$em->flush();

			$respuesta= "OK";
		}

	   return new JsonResponse(array('mje' => $respuesta));
  }

  /**
  * Ajax Pagos
  *
  * @Route("/ajaxServicios/imprimirFactura", name="ajax_imprimir_factura")
  *
  * @param Request $request
  *
  * @return Response
  */

  public function ajaxImprimirFacturaAction(Request $request){
  		
	   return new JsonResponse(array('mje' => $respuesta));
  }











 /**
  * Render admin page.
  *
  * Route("admin/{page}", name="admin_page", defaults={"page": "plain"})
  *
  * @param Request $request Request
  * @param string  $page    Page name
  *
  * @return Response
  */
 public function adminAction(Request $request, $page = 'plain')
 {
     $templateName = basename(sprintf('admin/pages/%s.html.twig', $page));
     if ($templateName !== sprintf('%s.html.twig', $page)) {
         throw $this->createNotFoundException('Page not found');
     }
     if (!$this->get('templating')->exists(sprintf('admin/pages/%s', $templateName))) {
         $templateName = 'plain.html.twig';
     }

     return $this->render(sprintf('admin/pages/%s', $templateName));
 }

 /**
  * Redirect to admin homepage which is currently index (plain page).
  *
  * Route("/", name="homepage")
  *
  * @param Request $request Request
  *
  * @return Request
  */
 public function homePageAction(Request $request)
 {
     return $this->redirectToRoute('ecotour_page', ['page' => 'index']);
 }
}
