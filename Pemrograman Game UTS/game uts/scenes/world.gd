extends Node2D

@onready var heartContainer = $CanvasLayer/heartContainer
@onready var player = $TileMap/player
@onready var backsound = $backsound
# Called when the node enters the scene tree for the first time.
func _ready():
	heartContainer.setMaxHearts(player.maxHealth)
	heartContainer.updateHearts(player.currentHealth)
	player.healthChanged.connect(heartContainer.updateHearts)

# Called every frame. 'delta' is the elapsed time since the previous frame.
func _process(delta):
	pass


func _on_inventory_gui_closed():
	get_tree().paused = false


func _on_inventory_gui_opened():
	get_tree().paused = true
	backsound.stream_paused = false
