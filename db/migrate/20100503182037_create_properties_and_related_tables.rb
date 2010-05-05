class CreatePropertiesAndRelatedTables < ActiveRecord::Migration
  def self.up
    create_table :property_groups do |t|
      t.string :name, :null => false
      t.timestamps
    end
    
    create_table :properties do |t|
      t.string :name, :null => false
      t.string :type, :null => false
      t.references :property_group, :null => false
      t.timestamps
    end
    
    create_table :property_values do |t|
      t.text :value, :null => false
      t.references :property, :null => false
      t.references :disc, :null => false
      t.timestamps
    end
    
    create_table :media_types_property_groups, :id => false do |t|
      t.references :media_type, :null => false
      t.references :property_group, :null => false
      t.timestamps
    end
  end

  def self.down
    drop_table :media_types_property_groups
    drop_table :property_values
    drop_table :properties
    drop_table :property_groups
  end
end
